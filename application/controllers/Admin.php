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
        if ($datas != null) {
            foreach ($columns as $key => $value) {
                $columns[$key]['list_hide'] = strpos($datas->list_hide, $key) != false;
                $columns[$key]['get_hide'] = strpos($datas->get_hide, $key) > 0;
                $columns[$key]['create_hide'] = strpos($datas->create_hide, $key) > 0;
                $columns[$key]['edit_hide'] = strpos($datas->edit_hide, $key) > 0;
            }
        }
        $data = [
            "data" => $datas,
            "columns" => $columns,
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
            echo json_encode(['status' => 'error', 'message' => 'Columns bulunamadı.']);
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
                    ->set_output(json_encode(['status' => 'success', 'message' => 'Kayıt Başarılı.']))
                    ->_display();
                die();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Kayıt sırasında bir hata ile karşılaşıldı.', 'data' => $status]);
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
                    ->set_output(json_encode(['status' => 'success', 'message' => 'Kayıt Başarılı.']))
                    ->_display();
                die();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Kayıt sırasında bir hata ile karşılaşıldı.', 'data' => $status]);
                $this->output->set_status_header(400);
                die();
            }
        }
    }
}
