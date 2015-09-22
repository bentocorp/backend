# Simple Role Syntax
# ==================
# Supports bulk-adding hosts to roles, the primary
# server in each group is considered to be the first
# unless any hosts have the primary property set.
# Don't declare `role :all`, it's a meta role
role :app, %w{bento-prod-api1 bento-prod-api2}
#role :web, %w{some-other-thing}
#role :db,  %w{deploy@example.com}

# Set Branch
set :branch, 'master'

#require custom config
require './config/myconfig.rb'



namespace :deploy do

  desc 'Get stuff ready prior to symlinking'
  task :compile_assets do
    on roles(:app), in: :sequence, wait: 1 do
      execute "cp #{deploy_to}/../components/.env.php #{release_path}"
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
      upload! './.env.php', "#{deploy_to}/../components/.env.php"
      execute "cp #{deploy_to}/../components/.env.php #{release_path}"
    end
  end

end



