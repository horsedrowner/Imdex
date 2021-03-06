module Imdex
  module Helpers
    def u(value)
      URI.encode(value).gsub('+', '%2B')
    end

    def css_url(value)
      return nil if value.nil?
      CGI.escape(value).gsub('+', '%20')
    end

    ##
    # Determines the last modified date of the file corresponding to the 
    # specified template.
    #
    def mtime(name, engine)
      find_template settings.views, name, Tilt[engine] do |file|
        return File.mtime(file) if File.exist?(file)
      end
      Time.now
    end
  end
end