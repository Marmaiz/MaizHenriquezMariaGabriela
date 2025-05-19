<?php
/**
 * Clase Controlador
 * 
 * Esta clase actúa como el controlador en el patrón MVC, encargándose de:
 * - Recibir y procesar las solicitudes HTTP.
 * - Validar y gestionar los datos del formulario.
 * - Asignar variables a la vista (Smarty) y renderizar la plantilla correspondiente.
 *
 * @author Maria G. Maiz H.
 * @version 1
 */
class ControladorMGM
{
    /**
     * onInit: Función por defecto que recupera todos los libros de la base de datos y los muestra en una tabla
     *
     * @param \PDO $con conexión PDO con la Base de datos.
     * @param Smarty $smarty instancia de Smarty, gestiona las plantillas para las vistas.
     */
    public static function onInit(\PDO $con, \Smarty\Smarty $smarty)
    {
        $libros = Libros::listarMGM($con);
        $smarty->assign('libros', $libros);
        $smarty->display('index.tpl');     
    }

    /**
     * crear_libro_MGM: Realiza la inserción de objetos Libro, cuyos datos se introducen en un formulario, en la base de datos.
     *
     * @param \PDO $con conexión PDO con la Base de datos.
     * @param Smarty $smarty instancia de Smarty, gestiona las plantillas para las vistas.
     * @param Peticion $p instancia de clase Peticion, gestiona los parámetros recibidos vía GET y POST.
     * @internal La clase Peticion es proporcionada para facilitar el manejo a los datos del formulario
     * @return bool True si la eliminación fue exitosa, false de lo contrario.
     * @throws Exception Se lanza excepción si no existe el parámetro.
     */
    public static function crear_libro_MGM(\PDO $con, \Smarty\Smarty $smarty, Peticion $p)
    {
        try {
            // Validar que todos los datos requeridos estén presentes utilizando la clase Peticion
            if ($p->has('isbn', 'titulo', 'autor', 'anio_publicacion', 'paginas', 'ejemplares_disponibles')) {

                // Recoger y validar datos utilizando la clase Peticion
                $isbn = $p->getString('isbn');
                $titulo = $p->getString('titulo');
                $autor = $p->getString('autor');

                // Se lanzara una excepcion de la clase Peticion si estos campos no son números enteros
                $anio_publicacion = $p->getInt('anio_publicacion');
                $paginas = $p->getInt('paginas'); 
                $ejemplares_disponibles = $p->getInt('ejemplares_disponibles');

                // Variable Bool para comprobar que los campos tienen el formato válido
                $valido = true;
                // Cadena para almacenar los errores encontrados
                $errones_salida = "";

                // Valido los datos y almaceno el error si corresponde
                if ($ejemplares_disponibles <= 0) {
                    $valido = false;
                    $errones_salida = $errones_salida . "El número de ejemplares debe ser un número entero y mayor a 0. <br>";
                }

                if ($paginas <= 0) {
                    $valido = false;
                    $errones_salida = $errones_salida . "El número de páginas debe ser un número entero y mayor a 0. <br>";
                }

                if ($anio_publicacion > (int) date("Y")) {
                    $valido = false;
                    $errones_salida = $errones_salida . "El año de publicación debe ser un número entero y menor al año en curso. <br>";
                }

                if (!(strlen($isbn) === 13)) {
                    $valido = false;
                    $errones_salida = $errones_salida . "El ISBN debe tener 13 digitos. <br>";
                }

                // Si los datos son válidos continuo con la función 
                if ($valido) {

                    // Crear un nuevo libro y establecer sus valores
                    $libro = new Libro();
                    $libro->setIsbn($isbn);
                    $libro->setTitulo($titulo);
                    $libro->setAutor($autor);
                    $libro->setAnio_publicacion(intval($anio_publicacion));
                    $libro->setPaginas(intval($paginas));
                    $libro->setEjemplares_disponibles(intval($ejemplares_disponibles));

                    // Guardar en la base de datos
                    $resultado = $libro->guardar($con);

                    // Guardo en una variable el resultado de la operación guardar
                    if ($resultado === true) {
                        $salida = "Libro creado con éxito. ID: " . $libro->getId();
                    } else {
                        $salida = "Error al guardar el libro";
                    }
                } 
                // Si los datos no son válidos lanzo el mensaje con los errores
                else {
                    $salida = "Error al guardar el libro. Datos erroneos:<br>" . $errones_salida;
                    $resultado = false;
                }
            } 
            // Si no estan presentes todos los campos obligatorios lanzo el mensaje que corresponde
            else {
                $salida = "Error: Faltan datos obligatorios.";
                $resultado = false;
            }
        } catch (\Exception $e) {
            // Captura la excepción y almacena el mensaje de error
            $salida = "Error: " . $e->getMessage();
            $resultado = false;
        }

        // Muestro el resultado de la operación utilizando las plantillas Smarty
        $smarty->assign('salida', $salida);
        $smarty->display('salidas.tpl');

        // Devuelvo el resultado de la operación guardar o el mensaje de error
        return $resultado;
    }

}