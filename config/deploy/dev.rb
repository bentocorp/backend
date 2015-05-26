# Simple Role Syntax
# ==================
# Supports bulk-adding hosts to roles, the primary
# server in each group is considered to be the first
# unless any hosts have the primary property set.
# Don't declare `role :all`, it's a meta role
role :app, %w{bento-dev-api1 bento-dev-api2}
#role :web, %w{some-other-thing}
#role :db,  %w{deploy@example.com}

# Set Branch
set :branch, '1.2-Lunch'

#require custom config
require './config/myconfig.rb'



namespace :deploy do

  #desc 'Restart Dev Application'
  #task :restart do
  #  on roles(:app), in: :sequence, wait: 2 do
      # Your restart mechanism here, for example:
      #execute "cd #{release_path}/build_scripts && phing devtest"
      #execute "cp #{deploy_to}/../components/.env.dev.php #{release_path}"
      #execute "cp -r #{deploy_to}/../components/vendor #{release_path}"
    #end
  #end


  desc 'Get stuff ready prior to symlinking'
  task :compile_assets do
    on roles(:app), in: :sequence, wait: 1 do
      execute "cp #{deploy_to}/../components/.env.dev.php #{release_path}"
      #execute "pwd"
      #within release_path do
      #  execute "cd ./build_scripts"
      #end
    end
  end
  
  after :updated, :compile_assets

end


# Devops commands
namespace :ops do

  desc 'Copy non-git ENV specific files to servers.'
  task :put_env_components do
    on roles(:app), in: :sequence, wait: 1 do
      upload! './.env.dev.php', "#{deploy_to}/../components/.env.dev.php"
      execute "cp #{deploy_to}/../components/.env.dev.php #{release_path}"
    end
  end

end



