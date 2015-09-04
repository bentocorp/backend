# config valid only for current version of Capistrano
lock '3.3.5'

set :application, 'bento-backend'
set :repo_url, 'git@github.com:bentocorp/backend.git'

# Set up a strategy to deploy only a project directory (not the whole repo)
#set :git_strategy, RemoteCacheWithProjectRootStrategy
#set :project_root, 'app'

# Default branch is :master
# ask :branch, proc { `git rev-parse --abbrev-ref HEAD`.chomp }

# Set Version
api_version = 'v2'

# Default deploy_to directory is /var/www/my_app
deploy_to = "/sites/bento-backend/#{api_version}/deploy"
set :deploy_to, deploy_to

components_dir = "/sites/bento-backend/#{api_version}/components"
set :components_dir, components_dir

# Default value for :scm is :git
# set :scm, :git

# Default value for :format is :pretty
# set :format, :pretty

# Default value for :log_level is :debug
# set :log_level, :debug

# Default value for :pty is false
# set :pty, true

# Default value for :linked_files is []
# set :linked_files, %w{config/database.yml}

# Default value for linked_dirs is []
# set :linked_dirs, %w{bin log tmp/pids tmp/cache tmp/sockets vendor/bundle public/system}

# Default value for default_env is {}
# set :default_env, { path: "/opt/ruby/bin:$PATH" }

# Default value for keep_releases is 5
# set :keep_releases, 5

namespace :deploy do

  desc 'Restart application'
  task :restart do
    on roles(:app), in: :sequence, wait: 2 do
      # Your restart mechanism here, for example:
      # execute :touch, release_path.join('tmp/restart.txt')
      execute "touch #{deploy_to}/sanity-check.txt"
      execute "cd #{release_path} && sudo chmod -R 775 app/storage"
      execute "sudo service nginx reload && sudo service php5-fpm reload"
      #execute "cd #{release_path} && php composer.phar install"
      #execute "cd #{release_path}/build_scripts && phing stage"
      #execute "pwd"
      #within release_path do
      #  execute "cd ./build_scripts"
      #end
    end

  end

  after :publishing, :restart

  after :restart, :clear_cache do
    on roles(:web), in: :groups, limit: 3, wait: 10 do
      # Here we can do anything such as:
      # within release_path do
      #   execute :rake, 'cache:clear'
      # end
    end

    #on roles(:app), in: :groups, limit: 3, wait: 5 do
      invoke 'cmd:memcache_flush'
    #end

  end

end


# Server commands
namespace :cmd do

  desc 'Flush Memcache'
  task :memcache_flush do
    on roles(:app), in: :sequence, wait: 1 do
      execute 'echo "flush_all" | nc localhost 11211'
    end
  end
  
end


# Devops commands
namespace :ops do

  desc 'Copy non-git files to servers.'
  task :put_components do
    on roles(:app), in: :sequence, wait: 1 do
      #system("tar -zcf ./build/vendor.tar.gz ./vendor ")
      upload! './build/vendor.tar.gz', "#{components_dir}", :recursive => true
      execute "cd #{components_dir}
       tar -zxf /sites/bento-backend/components/vendor.tar.gz"
    end
  end
  
end




