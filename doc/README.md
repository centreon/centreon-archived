# Centreon documentation

The official **Centreon documentation** can be found [here](https://documentation.centreon.com/docs/centreon/en/latest/)

It is possible to build the **official** Centreon documentation if you want to visualize it
or make some modifications.

## Prerequisite

You will need to install a python dependency.

On **debian based** distributions
```
sudo apt-get install python-sphinx
```
On **RedHat based** distributions
```
sudo yum install python-sphinx
```
On **Arch based** distributions
```
sudo pacman -S python-sphinx
```

## Compile

Once the dependency installed you will have to compile the documentation either the
French or English version. For the example lets compile the English version.

Go to the directory:
```
cd en/
```

Then compile:
```
make clean ; make html
```

We can use python to serve the `_build/html` directory and visualize the built documentation

If you are running **python2**:
```
cd _build/html
python -m SimpleHTTPServer
```
If you are running **python3**:
```
cd _build/html
python3 -m http.server
```
