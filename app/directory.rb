module Imdex
  class Directory
    attr_reader :name, :path

    def initialize(path)
      fail "Directory does not exist: #{ path }" unless Dir.exist?(path)

      @path = path
      @name = File.basename(path)
    end

    def entries
      Dir.entries(path, encoding: 'UTF-8')
    end

    def directories
      entries.select { |item| include_dir?(item) }
    end

    def files
      entries.select { |item| include_file?(item) }
    end

    def images
      files.select { |item| include_image?(item) }
    end

    def count
      entries.length
    end

    private
    def include_dir?(item)
      item_path = File.expand_path(item, @path)
      File.directory?(item_path) unless item == '.'
    end

    def include_file?(item)
      item_path = File.expand_path(item, @path)
      File.file?(item_path)
    end

    def include_image?(item)
      item_path = File.expand_path(item, @path)
      (item_path =~ (/\.(jpe?g|png|gif|bmp)$/)) != nil
    end
  end
end