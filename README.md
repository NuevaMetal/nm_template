# README #

### Qué es este repositorio ###

* Tema oficial de la app web NuevaMetal
* 1.1.0

### Preparar el entorno ###

* Instalar ruby y compass
	sudo apt-get install ruby
	sudo gem update --system
	sudo apt-get install ruby1.9.1-dev
	sudo gem install compass
	sudo gem install rake

* Una vez instalado compass ya podrías compilar el scss situándote dentro del d
	/nm_template $> rake watch_scss

* Necesitas instalar una librería gráfica para poder utilizar el editor de imágenes
	apt-get install php5-imagick php5-gd



### Modo de trabajo ###

* Rama en local
Se trabaja sobre una rama en local, y cuando esté la tarea lista se debe hacer un
merge --squash sobre la rama de desarrollo 'dev' para finalmente dejar un solo commmit.
Una vez probado en dev y asegurándonos de que todo está bien entonces se podrá hacer un merge
con master para subirlo a producción.

