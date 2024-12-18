# 🍕 Sistema de Gestión de Pizzería

¡Hola! 👋 Este es un programa para ayudarte a manejar tu negocio de pizzas. Vamos a instalarlo paso a paso, ¡es más fácil de lo que parece! 
## 📚 Documentación

- [Documentación de Pruebas](TESTING.md)

## 📋 Lo que necesitas antes de empezar

Imagina que vas a cocinar una pizza: primero necesitas tener todos los ingredientes listos. Aquí te decimos todo lo que necesitas tener en tu computadora:

### Programas necesarios
- ⭐ XAMPP (incluye Apache, MySQL y PHP)
  - Descárgalo de: https://www.apachefriends.org/es/index.html
  - Necesitamos PHP versión 7.4 o más nueva
- 📝 Visual Studio Code (VS Code)
  - Es como un cuaderno especial para escribir código
  - Descárgalo de: https://code.visualstudio.com/
- 🔄 Git
  - Es una herramienta que nos ayuda a guardar y compartir nuestro trabajo
  - Descárgalo de: https://git-scm.com/downloads

## 🎮 Paso a paso: Instalación del proyecto

### Paso 1: Preparar XAMPP
1. 🚀 Abre XAMPP Control Panel
2. ✅ Presiona "Start" en Apache y MySQL
   - Deberías ver una luz verde en ambos
   - Si ves una luz roja, algo no está bien. ¡Pide ayuda!

### Paso 2: Traer el programa a tu computadora
Abre el programa llamado "Terminal" (en Mac/Linux) o "Símbolo del sistema" (en Windows) y escribe:

```bash
git clone https://github.com/Bjohan23/pizza4-version-antigua.git
```

### Paso 3: Cambiar el nombre de la carpeta
Es importante usar el nombre correcto para que todo funcione bien.

Si usas Windows, escribe:
```bash
ren pizza4-version-antigua pizza4
```

Si usas Mac o Linux, escribe:
```bash
mv pizza4-version-antigua pizza4
```

### Paso 4: Entrar a la carpeta
```bash
cd pizza4
```

### Paso 5: Preparar la base de datos
Ahora vamos a crear un lugar donde guardar toda la información de las pizzas y pedidos.

Si usas Mac o Linux:
```bash
# Dar permiso para ejecutar el script
chmod +x setup_database.sh

# Ejecutar el script
./setup_database.sh
```

Si usas Windows, simplemente ejecuta:
```bash
setup_database.sh
```

### Paso 6: Abrir el proyecto en VS Code
```bash
code .
```

> 🎈 **¿No funciona el comando "code ."?** ¡No hay problema! 
> Puedes:
> 1. Abrir VS Code normalmente
> 2. Hacer clic en "Archivo" o "File"
> 3. Seleccionar "Abrir Carpeta" o "Open Folder"
> 4. Buscar y seleccionar la carpeta "pizza4"

## 🔍 Verificar que todo está bien

Antes de usar el programa, asegúrate de que:
1. 🟢 XAMPP está corriendo (luces verdes en Apache y MySQL)
2. 📂 La carpeta se llama exactamente "pizza4"
3. 🗄️ El script de la base de datos se ejecutó sin errores
4. 💻 PHP versión 7.4 o superior está instalado

Para verificar tu versión de PHP:
1. Abre XAMPP Control Panel
2. Presiona el botón "Shell"
3. Escribe: `php -v`
4. Deberías ver un número 7.4 o más alto

## 🚨 Solución de problemas comunes

- **XAMPP no inicia**: Asegúrate de que no hay otros programas usando los puertos 80 o 3306
- **Error en la base de datos**: Verifica que MySQL está corriendo en XAMPP
- **Página en blanco**: Revisa que Apache está corriendo en XAMPP
- **Errores de PHP**: Asegúrate de tener PHP 7.4 o superior instalado

## 🎯 ¿Necesitas ayuda?
Si algo no funciona:
- 🤔 No te preocupes, ¡es normal tener problemas la primera vez!
- 🙋‍♂️ Puedes pedir ayuda a alguien con más experiencia
- 📝 Revisa dos veces que seguiste todos los pasos
- ⚡ Asegúrate de que XAMPP está encendido antes de usar el programa

## 🌟 ¡Felicitaciones!
Si llegaste hasta aquí y todo funciona, ¡ya puedes empezar a usar tu sistema de pizzería!