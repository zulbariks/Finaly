<?php
require_once __DIR__ . "/model.php";

// define("TABLE","cars");
class Posts extends Model
{
  protected $table = 'posts';
  protected $primary_key = "id_post";
  public function create($datas)
  {
    $tags_id = $datas['post']['tag_id_pivot'];

    if ($datas['files']['attachment_post']['name'] == "") {
      return "Masukkan gambar terlebih dahulu";
    }

    $nama_file = $datas["files"]["attachment_post"]["name"];
    $tmp_name = $datas["files"]["attachment_post"]["tmp_name"];
    $ekstensi_file = pathinfo($nama_file, PATHINFO_EXTENSION);
    $ekstensi_allowed = ['jpg', 'jpeg', 'png', 'gif', 'heic', 'raw'];
    if (!in_array($ekstensi_file, $ekstensi_allowed)) {
      return "Error: " . $ekstensi_file;
    }

    if ($datas["files"]["attachment_post"]["size"] > 6000000) {
      return "Ukuran file yang anda masukkan terlalu besar";
    }

    $nama_file = random_int(1000,  9999) . "." . $ekstensi_file;
    move_uploaded_file($tmp_name, "./../assets/img/posts/" . $nama_file);
    $datas = [
      "tittle" => $datas["post"]["tittle"],
      "content" => $datas["post"]["content"],
      "user_id" => $datas["post"]["user_id"],
      "category_id" => $datas["post"]["category_id"],
      "attachment_post" => $nama_file
    ];
    parent::create_data($datas, $this->table);

    $query_id = mysqli_insert_id($this->db);
    foreach ($tags_id as $tag) {
      $query = "INSERT INTO pivot_posts_tags (post_id_pivot, tag_id_pivot) VALUES ('$query_id', '$tag')";
      $result = mysqli_query($this->db, $query);
    }
    return $datas;
  }


  public function all()
  {
    return parent::all_data($this->table);
  }
  public function find($id)
  {
    $query = "SELECT * FROM posts JOIN categories ON posts.category_id = categories.id_category JOIN users on posts.user_id = users.id_user WHERE {$this->primary_key} = $id";
    $result = mysqli_query($this->db, $query);
    return $this->convert_data($result);
  }
  public function update($id, $datas)
  {
    $tags_id = $datas['post']['tag_id_pivot'];
    $attachment = "";
    if ($datas["files"]["attachment_post"]["name"] !== "") {
      $nama_file = $datas["files"]["attachment_post"]["name"];
      $tmp_name = $datas["files"]["attachment_post"]["tmp_name"];
      $ekstensi_file = pathinfo($nama_file, PATHINFO_EXTENSION);
      $ekstensi_allowed = ['jpg', 'jpeg', 'png', 'gif', 'heic', 'raw'];
      if (!in_array($ekstensi_file, $ekstensi_allowed)) {
        return "Error: ektensi" . $ekstensi_file . "Tidak diperbolehkan";
      }

      if ($datas["files"]["attachment_post"]["size"] > 5000000) {
        return "Ukuran file yang anda masukkan terlalu besar";
      }
      $nama_file = random_int(1000, 9999) . "." . $ekstensi_file;
      move_uploaded_file($tmp_name, "./../assets/img/posts/" . $nama_file);
      $attachment = $nama_file;
    }
    $datas = [
      "tittle" => $datas["post"]["tittle"],
      "user_id" => $datas["post"]["user_id"],
      "category_id" => $datas["post"]["category_id"], 
      "content" => $datas["post"]["content"]
    ];
    if ($attachment !== '') {
      $datas['attachment_post'] = $attachment;
    }
    parent::update_data($id, $this->primary_key, $datas, $this->table);
    $query_delete = "DELETE FROM pivot_posts_tags WHERE post_id_pivot = '$id'";
    $result_delete = mysqli_query($this->db, $query_delete);

    foreach ($tags_id as $tag) {
      $query_insert = "INSERT INTO pivot_posts_tags (post_id_pivot, tag_id_pivot) VALUES ('$id', '$tag')";
      $result = mysqli_query($this->db, $query_insert);
    };
  }
  public function delete($id)
  {
    $query_delete = "DELETE FROM pivot_posts_tags WHERE post_id_pivot = '$id'";
    $result_delete = mysqli_query($this->db, $query_delete);
    parent::delete_data($id, $this->primary_key, $this->table);
    return true;
  }
  public function search($keyword, $startData = null, $limit = null)
  {
    $queryLimit = "";
    if (isset($limit) && isset($startData)) {
      $queryLimit = "LIMIT $startData, $limit";
    }
    $keyword = "WHERE tittle LIKE '%{$keyword}%' $queryLimit";
    $query = "SELECT * FROM posts JOIN categories ON posts.category_id = categories.id_category JOIN users on posts.user_id = users.id_user $keyword";
    $result = mysqli_query($this->db, $query);
    return $this->convert_data($result);
  }
  public function pagginate($startData, $limit, $order)
  {
    $query = "SELECT * FROM {$this->table} $order LIMIT $startData, $limit";
    $order = " order by tittle";
    $result = mysqli_query($this->db, $query);
    return $this->convert_data($result);
  }

  public function all2($starData, $limit)
  {
    $query =
      "SELECT * FROM posts JOIN categories ON posts.category_id = categories.id_category JOIN users on posts.user_id = users.id_user LIMIT $starData , $limit ";
    $result = mysqli_query($this->db, $query);
    return $this->convert_data($result);
  }
  public function create_tags($tags_id)
  {
    $query_id = mysqli_insert_id($this->db);
    foreach ($tags_id as $tag) {
      $query = "INSERT INTO pivot_posts_tags (post_id_pivot, tag_id_pivot) VALUES ('$query_id', '$tag')";

      $result = mysqli_query($this->db, $query);
    }
  }
  public function NewPost()
  {
    $query = "SELECT * FROM posts ORDER BY posts.id_post DESC ";
    $result = mysqli_query($this->db, $query);
    return $this->convert_data($result);
  }

  public function FindPostAsBlog($id_post)
  {
    $query = "SELECT posts.*, categories.*, users.*, pivot_posts_tags.*,  tags.* FROM posts JOIN categories ON posts.category_id = categories.id_category JOIN users ON posts.user_id = users.id_user JOIN pivot_posts_tags ON pivot_posts_tags.post_id_pivot = posts.id_post JOIN tags ON pivot_posts_tags.tag_id_pivot = tags.id_tag WHERE posts.id_post = $id_post";
    $result = mysqli_query($this->db, $query);
    return $this->convert_data($result);
  }
}


// "SELECT posts.id_post, posts.content, posts.attachment_post, posts.tittle, categories.name_category, posts.user_id, users.full_name, users.avatar, pivot_posts_tags.post_id_pivot, pivot_posts_tags.tag_id_pivot, tags.name_tag FROM posts JOIN categories ON posts.category_id = categories.id_category JOIN users ON posts.user_id = users.id_user JOIN pivot_posts_tags ON pivot_posts_tags.post_id_pivot = posts.id_post JOIN tags ON pivot_posts_tags.tag_id_pivot = tags.id_tag";