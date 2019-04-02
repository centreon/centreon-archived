To start the monitoring engine :

1. Log in to the ‘root’ user on your remote server.
2. Start Centreon Broker ::

     # systemctl start cbd

3. Start Centreon Engine ::

     # systemctl start centengine

4. Start centcore ::

    # systemctl start centcore

5. Start centreontrapd ::

    # systemctl start centreontrapd


To make services automatically start during system bootup run these commands
on the remote server: ::

    # systemctl enable centcore
    # systemctl enable centreontrapd
    # systemctl enable cbd
    # systemctl enable centengine
    # systemctl enable centreon
