<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getAuthsData($table_name, $auths_group_id)
    {
        if ($this->input->method() == 'options') {
            $this->output->set_content_type('application/json', 'utf-8')
                ->set_output('get')
                ->_display();
            die();
        }
        if ($this->input->method() != 'get') {
            $this->output->set_status_header(405);
            die();
        }

        $this->load->model('table_model');
        $this->table_model->tableName = 'auths';
        $datas = $this->table_model->get(array("table_name" => $table_name, 'auths_group_id' => $auths_group_id));

        $columns = $this->table_model->columns($table_name);


        $auths_column = $this->table_model->columns('auths');
        if ($datas != null) {
            foreach ($columns as $key => $value) {
                $columns[$key]['list_hide'] = strpos($datas->list_hide, $key) != false;
                $columns[$key]['get_hide'] = strpos($datas->get_hide, $key) > 0;
                $columns[$key]['create_hide'] = strpos($datas->create_hide, $key) > 0;
                $columns[$key]['edit_hide'] = strpos($datas->edit_hide, $key) > 0;
            }
        }
        foreach ($auths_column as $key => $value) {

            if ($value['key'] == 'MUL' && !empty($value['table_name'])) {

                $auths_column[$key]['list'] =
                    (array) $this->list($value['table_name'], $value['table_column']);
            }
        }
        unset($auths_column['id']);
        unset($auths_column['own_id']);
        unset($auths_column['user_id']);
        unset($auths_column['status']);
        unset($auths_column['added_date']);
        unset($auths_column['updated_date']);

        $data = [
            "data" => $datas,
            'columns' => $auths_column,
            "table_columns" => $columns,
            "status" => "success"
        ];

        header('Content-Type: application/json');
        $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data))
            ->_display();
        die();
    }
    public function setAuthsData($table_name, $auths_group_id)
    {
        $post = $this->input->post();
        $get = (array) json_decode($this->input->raw_input_stream);
        $input = array();
        if (empty($post) == 1) {
            $input = $get;
        } else {
            $input = $post;
        }

        if (empty($input['columns'])) {
            echo json_encode(['status' => 'error', 'message' => 'Columns bulunamad??.']);
            $this->output->set_status_header(400);
            die();
        }


        $list_hide = '';
        $get_hide = '';
        $create_hide = '';
        $edit_hide = '';
        foreach ($input['columns'] as $key => $value) {
            if ($value->list_hide == 1) {
                $list_hide = $list_hide . ',' . $key;
            }
            if ($value->get_hide == 1) {
                $get_hide = $get_hide . ',' . $key;
            }
            if ($value->create_hide == 1) {
                $create_hide = $create_hide . ',' . $key;
            }
            if ($value->edit_hide == 1) {
                $edit_hide = $edit_hide . ',' . $key;
            }
        }
        $this->load->model('table_model');
        $this->table_model->tableName = 'auths';
        $datas = $this->table_model->get(array("table_name" => $table_name, 'auths_group_id' => $auths_group_id));

        if ($datas == null) {
            $status = $this->table_model->add([
                "table_name" => $table_name,
                'auths_group_id' => $auths_group_id,
                "name" => $input['name'],
                "list_access" => $input['list_access'],
                "create_access" => $input['create_access'],
                "edit_access" => $input['edit_access'],
                "list_hide" => $list_hide,
                "get_hide" => $get_hide,
                "create_hide" => $create_hide,
                "edit_hide" => $edit_hide
            ]);
            if ($status) {
                header('Content-Type: application/json');
                $this->output
                    ->set_content_type('application/json', 'utf-8')
                    ->set_output(json_encode(['status' => 'success', 'message' => 'Kay??t Ba??ar??l??.']))
                    ->_display();
                die();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Kay??t s??ras??nda bir hata ile kar????la????ld??.', 'data' => $status]);
                $this->output->set_status_header(400);
                die();
            }
        } else {
            $status = $this->table_model->update(array("table_name" => $table_name, 'auths_group_id' => $auths_group_id), [
                "name" => $input['name'],
                "list_access" => $input['list_access'],
                "create_access" => $input['create_access'],
                "edit_access" => $input['edit_access'],
                "list_hide" => $list_hide,
                "get_hide" => $get_hide,
                "create_hide" => $create_hide,
                "edit_hide" => $edit_hide
            ]);
            if ($status) {
                header('Content-Type: application/json');
                $this->output
                    ->set_content_type('application/json', 'utf-8')
                    ->set_output(json_encode(['status' => 'success', 'message' => 'Kay??t Ba??ar??l??.']))
                    ->_display();
                die();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Kay??t s??ras??nda bir hata ile kar????la????ld??.', 'data' => $status]);
                $this->output->set_status_header(400);
                die();
            }
        }
    }
    public function getTableList()
    {
        $this->load->model('admin_model');
        $data = $this->admin_model->tables();


        header('Content-Type: application/json');
        $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data))
            ->_display();
        die();
    }
    public function addColumn($table_name)
    {

        $input = (array) json_decode($this->input->raw_input_stream);

        $this->load->dbforge();
        $example = array(
            $input['name'] => array(
                'type' => $input['type'],
                'constraint' => $input['constraint'],
                'unsigned' => $input['unsigned'],
                'auto_increment' => $input['auto_increment'],
                'unique' => $input['unique'],
                'default' => $input['default'],
                'comment' => $input['comment'],
                'null' => $input['null'],
            ),
        );
        $state = $this->dbforge->add_column($table_name, $example);
        if ($state) {
            $this->output->set_status_header(200);
            $this->output->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode(['status' => 'success']))
                ->_display();
            die();
        } else {
            $this->output->set_status_header(400);
        }
    }
    public function list($table_name, $column)
    {
        $this->table_model->tableName = $table_name;
        $datas = $this->table_model->get_all([], [], 1000);
        $newData = [];
        foreach ($datas as $key => $value) {
            $newData[$key] = [
                'id' => $value->id,
                'label' => $value->$column
            ];
        }
        return $newData;
    }
}
