<?php

class DB {
    static private function connect() {
        $conn = new mysqli("localhost", "usr", "", "tests");

        if ($conn->connect_error) {
            die ("Connection failed: " . $conn->connect_error);
        }

        return $conn;
    }

    static private function disconnect($conn) {
        $conn->close();
    }

    static public function ResetTodo($id) {
        $conn = static::connect();
        if (!$conn->query("UPDATE labels SET todo = 0 WHERE id = {$id}")) {
            die ("Update table failed: " . $conn->error);
        }
        static::disconnect($conn);
    }

    static public function CreateLabelsTable() {
        $conn = static::connect();
        if (!$conn->query("DROP TABLE IF EXISTS labels") ||
            !$conn->query("CREATE TABLE labels(id INT NOT NULL AUTO_INCREMENT, todo INT, label VARCHAR(5), PRIMARY KEY (`id`))") ||
            !$conn->query("INSERT INTO labels(todo, label) VALUES (1, 'red')") ||
            !$conn->query("INSERT INTO labels(todo, label) VALUES (2, 'blue')") ||
            !$conn->query("INSERT INTO labels(todo, label) VALUES (0, 'green')") ||
            !$conn->query("INSERT INTO labels(todo, label) VALUES (3, 'black')")) {
            die ("Create table failed: " . $conn->error);
        }
        static::disconnect($conn);
    }

    static public function GetLabelsData() {
        $conn = static::connect();

        if (!$result = $conn->query("SELECT id, todo, label FROM labels")) {
            die ("Query failed: " . $conn->error );
        }

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data [] = $row;
        }

        $result->free();
        static::disconnect($conn);

        return $data;
    }
}

class Label {
    public function __construct($properties = []){
        foreach($properties as $key => $value){
            $this->{$key} = $value;
        }
    }
}

class Labels {
    function __construct() {
        DB::CreateLabelsTable();
    }

    public function ToDo() {
        foreach (DB::GetLabelsData() as $row) {
            $label = new Label($row);
            $todo = ToDoFactory::create($row['todo']);
            if ($todo != null)
                $todo->DoIt($label);
        }
    }
}

abstract class ToDoTypes {
    const BURN = 1;
    const HIDE = 2;
    const SHOW = 3;
}

class ToDoFactory {
    static function create($type) {
        switch ($type) {
            case ToDoTypes::BURN:
                return new Burn();
                break;
            case ToDoTypes::HIDE:
                return new Hide();
                break;
            case ToDoTypes::SHOW:
            default:
                return new Show();
                break;
        }
    }
}

interface ITodo { // todo
    public function DoIt($label);
}

class Burn implements ITodo {
    public function DoIt($label) {
        DB::ResetTodo($label->id);
        printf("The %s label is burned\n", $label->label);
    }
}

class Hide implements ITodo {
    public function DoIt($label) {
        DB::ResetTodo($label->id);
        printf("The %s label is hidden\n", $label->label);
    }
}

class Show implements ITodo {
    public function DoIt($label) {
        DB::ResetTodo($label->id);
        printf("The %s label is shown\n", $label->label);
    }
}

$labels = new Labels();
$labels->ToDo();