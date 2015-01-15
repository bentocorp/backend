# Simple Role Syntax
# ==================
# Supports bulk-adding hosts to roles, the primary
# server in each group is considered to be the first
# unless any hosts have the primary property set.
# Don't declare `role :all`, it's a meta role
role :app, %w{bento-prod-api1 bento-prod-api2}
#role :web, %w{some-other-thing}
#role :db,  %w{deploy@example.com}

#require custom config
require './config/myconfig.rb'



namespace :deploy do

  desc 'Restart Prod Application'
  task :restart do
    on roles(:app), in: :sequence, wait: 2 do
      # Your restart mechanism here, for example:
      #execute "cd #{release_path}/build_scripts && phing devtest"
      execute "cp #{deploy_to}/../components/.env.php #{release_path}"
      #execute "cp -r #{deploy_to}/../components/vendor #{release_path}"
    end
  end

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



