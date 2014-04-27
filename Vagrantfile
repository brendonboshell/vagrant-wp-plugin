# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.box = "hashicorp/precise32"
  config.vm.provision :shell, :path => "bootstrap.sh"
  config.vm.network :forwarded_port, host: 8080, guest: 80
  config.vm.provider "virtualbox" do |v|
    v.memory=2048
  end

  config.vm.synced_folder "../working-copy/trunk", "/plugin"
end
