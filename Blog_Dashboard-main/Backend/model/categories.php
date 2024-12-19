<?php
require_once __DIR__ . "/model.php";

// define("TABLE","cars");
class Categories extends Model
{
  protected $table = 'categories';
  protected $primary_key = "id_category";
  public function create($datas)
  {
    $nama_file = $datas["files"]["attachment_category"]["name"];
    $tmp_name = $datas["files"]["attachment_category"]["tmp_name"];
    $ekstensi_file = pathinfo($nama_file, PATHINFO_EXTENSION);
    $ekstensi_allowed = ['jpg', 'jpeg', 'png', 'gif', 'heic', 'raw'];
    if (!in_array($ekstensi_file, $ekstensi_allowed)) {
      return "Error: " . $ekstensi_file;
    }

    if ($datas["files"]["attachment_category"]["size"] > 6000000) {
      return "Ukuran file yang anda masukkan terlalu besar";
    }
    $nama_file = random_int(1000, 9999) . "." . $ekstensi_file;
    move_uploaded_file($tmp_name, "./../assets/img/categories/" . $nama_file);
    $datas = [
      "name_category" => $datas["post"]["name_category"],
      "attachment_category" => $nama_file
    ];
    return parent::create_data($datas, $this->table);
  }


  public function all()
  {
    return parent::all_data($this->table);
  }
  public function find($id)
  {
    return parent::find_data($id, $this->primary_key, $this->table);
  }
  public function update($id, $datas)
  {
    $attachment = "";
    if ($datas["files"]["attachment_category"]["name"] !== "") {
      $nama_file = $datas["files"]["attachment_category"]["name"];
      $tmp_name = $datas["files"]["attachment_category"]["tmp_name"];
      $ekstensi_file = pathinfo($nama_file, PATHINFO_EXTENSION);
      $ekstensi_allowed = ['jpg', 'jpeg', 'png', 'gif', 'heic', 'raw'];
      if (!in_array($ekstensi_file, $ekstensi_allowed)) {
        return "Error: " . $ekstensi_file;
      }

      if ($datas["files"]["attachment_category"]["size"] > 5000000) {
        return "Ukuran file yang anda masukkan terlalu besar";
      }
      $nama_file = random_int(1000, 9999) . "." . $ekstensi_file;
      move_uploaded_file($tmp_name, "./../assets/img/categories/" . $nama_file);
      $attachment = $nama_file;
    }
    $datas = [
      "name_category" => $datas["post"]["name_category"]
    ];
    if ($attachment !== '') {
      $datas['attachment_category'] = $attachment;
    }
    return parent::update_data($id, $this->primary_key, $datas, $this->table);
  }
  public function delete($id)
  {

    return parent::delete_data($id, $this->primary_key, $this->table);
  }
  public function search($keyword, $startData = null, $limit = null)
  {
    $queryLimit = "";
    if (isset($limit) && isset($startData)) {
      $queryLimit = "LIMIT $startData, $limit";
    }
    $keyword = "WHERE name_category LIKE '%{$keyword}%' $queryLimit";
    return parent::search_data($keyword, $this->table);
  }
  public function pagginate($starData, $limit)
  {

    $keyword = "LIMIT $starData , $limit";
    return parent::paggination_data($starData, $limit, $this->table);
  }
}
