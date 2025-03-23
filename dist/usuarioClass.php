<?php


class Usuario {
    private $conex;

    public function __construct($conex) {
        $this->conex = $conex;
    }

    public function obtenerUsuarioPorId($id) {
        $sql = "SELECT * FROM usuarios WHERE IdUsuario = ?;";
        $stmt = $this->conex->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
            return $usuario;
        } else {
            return null;
        }
    }
/*
    public function buscarUsuario($texto, $idCurso) {
        $sql = '';
        $stmt = null;
        $todosLosCursos = -1;
    
        if ($texto != null && $idCurso != $todosLosCursos) {
           
            $sql = "SELECT u.* 
            FROM usuarios u 
            INNER JOIN usuarioscursos uc ON u.IdUsuario = uc.IdUsuario 
            WHERE (u.nombre LIKE ? OR u.apellido LIKE ? OR u.email LIKE ?) AND uc.IdCurso = ?";
    
            $texto = "%" . strtolower($texto) . "%";
            $stmt = $this->conex->prepare($sql);
            $stmt->bind_param("sssi", $texto, $texto, $texto, $idCurso);
        } else if ($texto != null && $idCurso == $todosLosCursos) {
          
            $sql = "SELECT * FROM usuarios WHERE nombre LIKE ? OR apellido LIKE ? OR email LIKE ?";
            $texto = "%" . strtolower($texto) . "%";
            $stmt = $this->conex->prepare($sql);
            $stmt->bind_param("sss", $texto, $texto, $texto);
        } else if ($texto == null) {
    
            if ($idCurso == $todosLosCursos) {
                $sql = "SELECT * FROM usuarios";
                $stmt = $this->conex->prepare($sql);
            } else {
                $sql = "SELECT u.* 
        FROM usuarios u 
        INNER JOIN usuarioscursos uc ON u.IdUsuario = uc.IdUsuario 
        WHERE uc.IdCurso = ?";
                $stmt = $this->conex->prepare($sql);
                $stmt->bind_param("i", $idCurso);
            }
        }
        $stmt->execute();
        $result = $stmt->get_result();
    
        $usuarios = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $usuarios[] = $row;
            }
        }
    
        return $usuarios;
    }*/
}

?>
