<?php include './header.php'; ?>

<link rel="stylesheet" href="assets/css/loader.css">
<style>
    .profile-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        overflow: hidden;
        background: #fff;
    }
    .profile-card .card-header {
        background: #28a745;
        color: #fff;
        padding: 20px;
        font-weight: 600;
        border: none;
    }
    .profile-card .card-body {
        padding: 30px;
    }
    .profile-img-container {
        position: relative;
        width: 150px;
        height: 150px;
        margin: 0 auto 30px;
    }
    .profile-img-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        border: 4px solid #f8f9fa;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .img-upload-btn {
        position: absolute;
        bottom: 5px;
        right: 5px;
        background: #ffc107;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #212529;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid #fff;
    }
    .img-upload-btn:hover {
        background: #e0a800;
        transform: scale(1.1);
    }
    .form-group label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
    }
    .form-control {
        border-radius: 8px;
        padding: 10px 15px;
        border: 1px solid #dee2e6;
    }
    .form-control:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.1);
    }
    .btn-update {
        background: #28a745;
        border: none;
        border-radius: 8px;
        padding: 10px 25px;
        font-weight: 600;
        color: #fff;
        transition: all 0.3s ease;
    }
    .btn-update:hover {
        background: #218838;
        transform: translateY(-2px);
        box-shadow: 0 5px 10px rgba(40, 167, 69, 0.2);
    }
    .security-section {
        margin-top: 40px;
        border-top: 1px solid #eee;
        padding-top: 40px;
    }
    /* Loader Overlay */
    #loader-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255,255,255,0.8);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
</style>

<div id="loader-overlay">
    <div class="loader"></div>
</div>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="profile-card card">
                <div class="card-header">
                    <i class="fas fa-user-edit mr-2"></i> Edit Admin Profile
                </div>
                <div class="card-body">
                    <!-- Profile Update Form -->
                    <form id="profileForm" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="profile-img-container">
                            <img id="previewImg" src="./src/images/<?= htmlspecialchars($user['profile_image'] ?? 'user-avtar.png') ?>" alt="Profile Image">
                            <label for="profile_image" class="img-upload-btn">
                                <i class="fas fa-camera"></i>
                            </label>
                            <input type="file" id="profile_image" name="profile_image" style="display: none;" accept="image/*">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label>Full Name</label>
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required placeholder="Enter full name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label>Email Address</label>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required placeholder="Enter email">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-4">
                                    <label>Mobile Number</label>
                                    <input type="text" name="mobile" class="form-control" value="<?= htmlspecialchars($user['mobile'] ?? '') ?>" placeholder="Enter mobile number">
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn-update">
                                <i class="fas fa-save mr-2"></i> Update Profile
                            </button>
                        </div>
                    </form>

                    <!-- Security Section -->
                    <div class="security-section">
                        <h5 class="mb-4" style="font-weight: 700; color: #343a40;">
                            <i class="fas fa-shield-alt mr-2 text-warning"></i> Change Password
                        </h5>
                        <form id="passwordForm">
                            <input type="hidden" name="action" value="change_password">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-4">
                                        <label>Current Password</label>
                                        <input type="password" name="current_password" class="form-control" required placeholder="••••••••">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-4">
                                        <label>New Password</label>
                                        <input type="password" name="new_password" class="form-control" required placeholder="••••••••">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-4">
                                        <label>Confirm New Password</label>
                                        <input type="password" name="confirm_password" class="form-control" required placeholder="••••••••">
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn-update bg-warning" style="color: #212529;">
                                    <i class="fas fa-key mr-2"></i> Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Image Preview
    $('#profile_image').change(function() {
        const file = this.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function(event) {
                $('#previewImg').attr('src', event.target.result);
            }
            reader.readAsDataURL(file);
        }
    });

    // Profile Form Submission
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        $('#loader-overlay').css('display', 'flex');

        $.ajax({
            url: 'profile_action.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                $('#loader-overlay').hide();
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: response.message
                    });
                }
            },
            error: function() {
                $('#loader-overlay').hide();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Something went wrong. Please try again later.'
                });
            }
        });
    });

    // Password Form Submission
    $('#passwordForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        $('#loader-overlay').css('display', 'flex');

        $.ajax({
            url: 'profile_action.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                $('#loader-overlay').hide();
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: response.message
                    });
                }
            },
            error: function() {
                $('#loader-overlay').hide();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Something went wrong. Please try again later.'
                });
            }
        });
    });
});
</script>

<?php include './footer.php'; ?>