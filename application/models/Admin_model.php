<?php

class Admin_model extends CI_Model
{
    public $create_table_referance = [
        'name' => '',
        'display' => ''
    ];
    /**
     * @param $data = 
     */
    public function createTable($data)
    {
        $name = $data['name'];
        $display = $data['display'];
        $query = "CREATE TABLE `Simple`.`$name` ( 
            `id` INT NOT NULL AUTO_INCREMENT COMMENT '" . $display . " ID' , 
            `status` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Status' , 
            `own_id` INT NOT NULL COMMENT 'Own User' , 
            `user_id` INT NOT NULL COMMENT 'Edited User' , 
            `added_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Added Time' , 
            `updated_date` DATETIME on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated Time' , 
            PRIMARY KEY (`id`)) ENGINE = InnoDB COMMENT = '" . $display . "';";
        $this->db->query($query);

        $CONSTRAINT1 = $name . '1_name';
        $CONSTRAINT2 = $name . '2_name';
        $query2 = "ALTER TABLE `$name` ADD CONSTRAINT `$CONSTRAINT1` FOREIGN KEY (`own_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT; ";
        $query3 = "ALTER TABLE `$name` ADD CONSTRAINT `$CONSTRAINT2` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;";

        $this->db->query($query2);
        $this->db->query($query3);
    }
    public function updateTable($data)
    {
        $name = $data['name'];
        $display = $data['display'];
        $old_name = $data['old_name'];
        $old_display = $data['old_display'];
        $query = "RENAME TABLE `Simple`.`$old_name` TO `Simple`.`$name`;";
        $query2 = "ALTER TABLE `$name` COMMENT = '$display';";
        $this->db->query($query);

        $this->db->query($query2);
    }
    public function deleteTable($table_name)
    {
        $query = "DROP TABLE $table_name;";
        $this->db->query($query);
    }
    public function update_columns($tableName, $column)
    {
        $query = "SELECT TABLE_NAME,COLUMN_NAME, IS_NULLABLE,DATA_TYPE,CHARACTER_MAXIMUM_LENGTH,PRIVILEGES,COLUMN_DEFAULT,COLUMN_COMMENT ,COLUMN_KEY
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME = '$tableName'
            AND TABLE_SCHEMA = 'Simple' AND COLUMN_NAME = '$column->name'";

        $information_schema = $this->load->database('information_schema', TRUE);
        $result = $information_schema->query($query)->result() or null;

        $clm_data =
            $query = "ALTER TABLE `'$tableName'` CHANGE `'$result->COLUMN_NAME'` `'$column->name'` VARCHAR(101) CHARACTER SET utf8 COLLATE utf8_turkish_ci NULL DEFAULT NULL COMMENT 'Title1';";
    }
}
