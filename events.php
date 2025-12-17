<?php
/* DATABASE CONNECTION */
$conn = mysqli_connect("localhost", "root", "", "bsit_portal");
if (!$conn) die("Connection failed: " . mysqli_connect_error());

$error = '';
$edit_mode = false;
$edit_id = 0;
$edit_title = '';
$edit_date = '';
$edit_description = '';
$edit_image = '';

if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $edit_mode = true;
    $edit_result = mysqli_query($conn, "SELECT * FROM events WHERE id=$edit_id");
    if ($edit_row = mysqli_fetch_assoc($edit_result)) {
        $edit_title = $edit_row['title'];
        $edit_date = $edit_row['event_date'];
        $edit_description = $edit_row['description'];
        $edit_image = $edit_row['image'];
    }
}

if (isset($_POST['add_event'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $event_date = mysqli_real_escape_string($conn, $_POST['event_date']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg','jpeg','png','gif','webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $error = "❌ Invalid image type.";
        } else {
            $new_image = time() . "_" . $_FILES['image']['name'];
            if (move_uploaded_file($_FILES['image']['tmp_name'], "public/images/".$new_image)) {
                mysqli_query($conn, "INSERT INTO events (title,event_date,description,image) VALUES ('$title','$event_date','$description','$new_image')");
                header("Location: events.php?success=1");
                exit();
            } else $error = "❌ Image upload failed.";
        }
    } else $error = "❌ No image selected.";
}

if (isset($_POST['update_event'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $event_date = mysqli_real_escape_string($conn, $_POST['event_date']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $image_sql = "";

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg','jpeg','png','gif','webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) $error = "❌ Invalid image type.";
        else {
            $new_image = time() . "_" . $_FILES['image']['name'];
            if (move_uploaded_file($_FILES['image']['tmp_name'], "public/images/".$new_image)) {
                $image_sql = ", image='$new_image'";
                if (!empty($edit_image) && file_exists("public/images/$edit_image")) unlink("public/images/$edit_image");
            }
        }
    }

    mysqli_query($conn, "UPDATE events SET title='$title', event_date='$event_date', description='$description' $image_sql WHERE id=$edit_id");
    header("Location: events.php?success=2");
    exit();
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $img_res = mysqli_query($conn, "SELECT image FROM events WHERE id=$id");
    if ($img_row = mysqli_fetch_assoc($img_res)) {
        if (!empty($img_row['image']) && file_exists("public/images/".$img_row['image'])) unlink("public/images/".$img_row['image']);
    }
    mysqli_query($conn, "DELETE FROM events WHERE id=$id");
    header("Location: events.php");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM events ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Events</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<style>
/* ===== GLOBAL ===== */
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: 'Roboto', sans-serif; background:#0a0f24; color:#fff; overflow-x:hidden; scroll-behavior:smooth; }
a { color: inherit; text-decoration:none; }

/* ----- HEADER ----- */
header {
  position: fixed; top:0; width:100%; height:70px; padding:12px 25px;
  display:flex; justify-content:space-between; align-items:center;
  background: rgba(17,27,63,0.95); border-bottom:1px solid #1e2a54; z-index:1100;
}
.logo { display:flex; align-items:center; gap:10px; }
.logo img { width:45px; height:45px; border-radius:50%; object-fit:cover; }
.logo span { font-size:20px; font-weight:700; color:#4cc9f0; }
.hamburger { font-size:30px; cursor:pointer; color:#cae7f0; }

/* ----- SIDE MENU ----- */
.side-menu {
  position: fixed; top:0; right:-280px; width:280px; height:100%;
  background:#0f1a3b; padding:30px; display:flex; flex-direction:column; gap:15px; transition:0.3s ease; z-index:2000;
}
.side-menu.open { right:0; }
.side-menu a { padding:12px 0; border-bottom:1px solid #1e2a54; font-weight:500; color:#fff; }

/* ----- ANNOUNCEMENT BANNER ----- */
.announcement-banner {
  margin-top:130px; text-align:center; padding:60px 20px 40px;
  background: linear-gradient(135deg,#4b79a1,#283e51); color:white;
}
.announcement-banner h1 { font-size:3rem; margin-bottom:10px; }
.announcement-banner .sub { opacity:0.8; font-size:1.2rem; }

/* ----- EVENTS CARDS ----- */

.description {
    font-size: 1rem;
    line-height: 1.6;
    margin-bottom: 15px; /* add spacing below description */
}

.action-buttons {
    display: flex;
    gap: 10px; /* space between Delete and Edit buttons */
}

.announcements-container {
  max-width:1000px; margin:40px auto; padding:20px;
  display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr)); gap:30px;
}
.announcement-card { background:white; color:black; border-radius:12px; overflow:hidden; box-shadow:0 6px 20px rgba(0,0,0,0.1); transition:0.3s; }
.announcement-card:hover { transform:translateY(-5px); box-shadow:0 15px 30px rgba(0,0,0,0.2); }
.thumb img { width:100%; height:auto; display:block; }
.content { padding:20px; }
.title { font-size:1.5rem; margin-bottom:8px; color:#283e51; }
.date { font-size:0.9rem; color:#555; margin-bottom:15px; }
.description { font-size:1rem; line-height:1.6; }

/* ----- ADMIN FORM ----- */
.admin-form {
  background: linear-gradient(135deg,#ffffff,#f3f6ff); color:#000; padding:30px;
  max-width:650px; margin:40px auto; border-radius:14px; box-shadow:0 20px 40px rgba(0,0,0,0.25);
}
.admin-form h2 { text-align:center; margin-bottom:20px; }
.admin-form input, .admin-form textarea {
  width:100%; padding:14px; margin-bottom:14px; border-radius:8px; border:1px solid #d0d7ff;
}
.admin-form textarea { resize:vertical; min-height:120px; }
.admin-form button {
  width:100%; padding:14px; border:none; border-radius:10px; cursor:pointer;
  background: linear-gradient(135deg,#4361ee,#4cc9f0); color:#fff;
}
.success { text-align:center; background:#e6fff4; color:#0f5132; padding:12px; border-radius:8px; margin-bottom:15px; font-weight:500; }

/* ----- FOOTER ----- */
footer { text-align:center; padding:18px; background:#0f1a3b; font-size:14px; color:#a8b4d4; margin-top:40px; }
</style>
</head>
<body>

<header>
  <div class="logo"><img src="public/images/bsitlogo.jpg" alt=""><span>BSIT Portal</span></div>
  <div class="hamburger" onclick="document.querySelector('.side-menu').classList.toggle('open')">&#9776;</div>
</header>

<div class="side-menu">
  <a href="#">Dashboard</a>
  <a href="#">Events</a>
  <a href="#">Announcements</a>
  <a href="#">Profile</a>
</div>

<section class="announcement-banner">
  <h1>Events</h1>
  <p class="sub">Stay updated with the latest happenings</p>
</section>

<div class="admin-form">
  <h2><?php echo $edit_mode ? 'Edit Event' : 'Add New Event'; ?></h2>
  <?php
  if (!empty($error)) echo "<p class='success'>$error</p>";
  if (isset($_GET['success'])) {
      $msg = $_GET['success']==1 ? "Event added successfully!" : "Event updated successfully!";
      echo "<p class='success'>$msg</p>";
  }
  ?>
  <form method="POST" enctype="multipart/form-data">
      <input type="text" name="title" placeholder="Event Title" required value="<?php echo htmlspecialchars($edit_title); ?>">
      <input type="text" name="event_date" placeholder="Event Date (e.g. Jan 31, 2025 · 6:00 PM)" required value="<?php echo htmlspecialchars($edit_date); ?>">
      <textarea name="description" placeholder="Event Description" required><?php echo htmlspecialchars($edit_description); ?></textarea>
      <input type="file" name="image" accept="image/*">

      <?php if ($edit_mode && $edit_image) { ?>
          <p>Current Image:</p>
          <img src="public/images/<?php echo $edit_image; ?>" style="width:120px; margin-bottom:10px; object-fit:cover;">
      <?php } ?>

      <button type="submit" name="<?php echo $edit_mode ? 'update_event' : 'add_event'; ?>">
          <?php echo $edit_mode ? 'Update Event' : 'Add Event'; ?>
      </button>
  </form>
</div>

<main class="announcements-container">
<?php
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
?>
  <div class="announcement-card">
    <div class="thumb"><img src="public/images/<?php echo $row['image']; ?>" alt=""></div>
    <div class="content">
      <h2 class="title"><?php echo htmlspecialchars($row['title']); ?></h2>
      <p class="date"><?php echo htmlspecialchars($row['event_date']); ?></p>
      <p class="description"><?php echo htmlspecialchars($row['description']); ?></p>
      <div class="content">
    <h2 class="title"><?php echo htmlspecialchars($row['title']); ?></h2>
    <p class="date"><?php echo htmlspecialchars($row['event_date']); ?></p>
    <p class="description"><?php echo htmlspecialchars($row['description']); ?></p>

    <div class="action-buttons">
        <a href="events.php?delete=<?php echo $row['id']; ?>" 
           style="color:white;background:#e63946;padding:5px 12px;border-radius:6px;" 
           onclick="return confirm('Are you sure?');">Delete</a>

        <a href="events.php?edit=<?php echo $row['id']; ?>" 
           style="color:white;background:#4361ee;padding:5px 12px;border-radius:6px;">Edit</a>
    </div>
</div>

    </div>
  </div>
<?php
    }
} else echo "<p style='text-align:center;color:#fff;'>No events available.</p>";
?>
</main>

<footer>
  &copy; <?php echo date('Y'); ?> BSIT Portal. All rights reserved.
</footer>

</body>
</html>
