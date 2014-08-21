require "sinatra/base"
require "better_errors"
require "sass"
require "yaml"
require "json"

require "./lib/helpers/template_utils"
require "./lib/imdex/directory"
require "./lib/imdex/image"

class ImdexApp < Sinatra::Base
  helpers Helpers::TemplateUtils

  configure do
    # Sinatra settings
    set :views, :sass => "styles", :default => "views"
    set :root, File.expand_path(File.dirname(File.dirname(__FILE__)))
    enable :static

    # App settings
    set :app_config, YAML.load_file("config/application.yaml") || {}

    # Directory settings
    Imdex::Image.public_folder = settings.public_folder
    Imdex::Directory.public_folder = settings.public_folder
    Imdex::Directory.include_pattern = Regexp.new(settings.app_config["include"]  || "\.(jpe?g|png|gif)$")
    Imdex::Directory.ignore_list = settings.app_config["ignore"] || [ "\\/\\.\\.?", "^\\/assets" ]
  end

  configure :development do
    use BetterErrors::Middleware
    BetterErrors.application_root = __dir__
  end

  get "/login" do
    redirect to("/auth/google")
  end

  get "/logout" do
    session.clear
    redirect to("/"), 303
  end

  post "/auth/:provider/callback" do
    info = request.env["omniauth.auth"]["info"]
    email = info["email"]
    name = info["name"]
    is_admin = settings.app_config["admins"].include?(email)

    session[:admin] = is_admin
    session[:email] = email
    session[:name] = name
    redirect to("/"), 303
  end

  post "/auth/failure" do
    session.clear
    redirect to("/"), 303
  end

  get "/styles/main" do
    last_modified style_modified(:main)
    sass :main
  end

  get "/styles/image" do
    last_modified style_modified(:image)
    sass :image
  end

  post "/delete" do
    halt 401, "Unauthorized" unless session[:admin]

    path = File.join(settings.public_folder, params[:path], params[:file])

    halt 404, "File does not exist" unless File.exists?(path)
    halt 405, "File is read-only" unless File.writable?(path)

    File.delete(path)

    status 204 # No Content
  end

  get "/*" do
    if params[:view]
      @name = params[:view]
      img = Imdex::Image.new(request.path, @name)

      erb :image, :layout => false, :locals => { :img => img }
    else
      dir = Imdex::Directory.new(request.path)

      @name = dir.name
      erb :directory, :locals => { :dir => dir }
    end
  end
end