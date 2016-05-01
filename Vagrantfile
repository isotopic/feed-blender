# -*- mode: ruby -*-
# vi: set ft=ruby :

# Windows users may need to install Microsoft Visual Studio C++

Vagrant.configure(2) do |config|

  config.vm.box = "scotch/box"
  config.vm.box_check_update = false
  config.vm.network "private_network", ip: "192.168.33.10"
  config.vm.network "public_network"
  config.vm.synced_folder ".", "/var/www", :mount_options => ["dmode=777", "fmode=666"]

  config.vm.provision "shell", inline: <<-SHELL
    echo "LC_ALL=\"en_US.UTF-8\"" >> /etc/environment
    source /etc/environment
    echo "Address: \033[1;37m http://192.168.33.10 \033[0m"
  SHELL

  #config.vm.provision "shell", path: "provision.sh"

end
