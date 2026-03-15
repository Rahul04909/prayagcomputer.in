<?php include '../header.php'; ?>

<link rel="stylesheet" href="../assets/css/loader.css">

<style>
    .admission-card { border: none; border-radius: 15px; box-shadow: 0 4px 25px rgba(0,0,0,0.08); overflow: hidden; margin-bottom: 60px; }
    .admission-header { background: #fff; border-bottom: 1px solid #f1f1f1; padding: 25px; }
    .admission-body { padding: 30px; background: #fff; }
    .section-title { font-weight: 700; color: #28a745; margin-bottom: 25px; display: flex; align-items: center; font-size: 18px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
    .section-title i { margin-right: 12px; background: #e8f5e9; padding: 10px; border-radius: 10px; }
    .form-label { font-weight: 600; color: #495057; margin-bottom: 8px; font-size: 14px; }
    .form-control, .form-select { border-radius: 10px; border: 1px solid #e0e0e0; padding: 10px 15px; background: #fcfcfc; transition: all 0.3s; }
    .form-control:focus, .form-select:focus { border-color: #28a745; background: #fff; box-shadow: 0 0 0 3px rgba(40,167,69,0.1); }
    .img-preview { width: 120px; height: 120px; object-fit: cover; border-radius: 15px; border: 2px dashed #ddd; padding: 5px; margin-top: 10px; }
    .software-access-box { background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #eee; }
    .password-toggle { cursor: pointer; color: #666; transition: color 0.2s; }
    .password-toggle:hover { color: #28a745; }
    #loader-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.7); display: none; align-items: center; justify-content: center; z-index: 9999; }
</style>

<div id="loader-overlay">
    <div class="loader"></div>
</div>

<?php
// Fetch Courses
try {
    $course_stmt = $pdo->query("SELECT id, title FROM courses WHERE status = 1 ORDER BY title ASC");
    $courses = $course_stmt->fetchAll();
} catch (PDOException $e) {
    $courses = [];
}
?>

<div class="content-header p-0"></div>

<section class="content mb-5">
    <div class="container-fluid">
        <form id="studentAdmissionForm" enctype="multipart/form-data">
            <div class="card admission-card">
                <div class="admission-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0" style="font-weight:800; color:#333;">New Student Admission</h4>
                    <a href="manage-students.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-list mr-1"></i> View Students</a>
                </div>
                <div class="admission-body">
                    <!-- Basic Information -->
                    <div class="section-title">
                        <i class="fas fa-user-graduate"></i> Basic Information
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-4">
                                <label class="form-label">Course Applied For <span class="text-danger">*</span></label>
                                <select name="course_id" class="form-select" required>
                                    <option value="">Select Course</option>
                                    <?php foreach ($courses as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-4">
                                <label class="form-label">Student Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="student_name" class="form-control" placeholder="Enter student name" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-4">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="example@mail.com">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-4">
                                <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text" style="border-radius: 10px 0 0 10px; background: #eee;">+91</span>
                                    <input type="text" name="mobile" class="form-control" placeholder="10 digit mobile" maxlength="10" pattern="\d{10}" required style="border-radius: 0 10px 10px 0;">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-4">
                                <label class="form-label">Father's Name</label>
                                <input type="text" name="father_name" class="form-control" placeholder="Enter father's name">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-4">
                                <label class="form-label">Mother's Name</label>
                                <input type="text" name="mother_name" class="form-control" placeholder="Enter mother's name">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-4">
                                <label class="form-label">Student Photo</label>
                                <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImg(this, 'stuPreview')">
                                <img id="stuPreview" src="../src/images/placeholder-user.png" class="img-preview" alt="Preview">
                            </div>
                        </div>
                    </div>

                    <!-- Security Details -->
                    <div class="section-title mt-4">
                        <i class="fas fa-lock"></i> Security Details
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-4">
                                <label class="form-label">Create Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
                                    <span class="input-group-text toggle-password" style="cursor:pointer;"><i class="fas fa-eye"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-4">
                                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm password" required>
                                    <span class="input-group-text toggle-password" style="cursor:pointer;"><i class="fas fa-eye"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Address Details -->
                    <div class="section-title mt-4">
                        <i class="fas fa-map-marker-alt"></i> Address Details
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-4">
                                <label class="form-label">Pincode</label>
                                <div class="input-group">
                                    <input type="text" id="pincode" name="pincode" class="form-control" placeholder="110001" maxlength="6">
                                    <button class="btn btn-success" type="button" id="lookupPincode"><i class="fas fa-search"></i></button>
                                </div>
                                <small id="pincodeLoader" class="text-success" style="display:none;"><i class="fas fa-spinner fa-spin"></i> Fetching details...</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-4">
                                <label class="form-label">Country</label>
                                <input type="text" id="country" name="country" class="form-control" value="India">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-4">
                                <label class="form-label">State</label>
                                <input type="text" id="state" name="state" class="form-control" placeholder="Select Pincode">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-4">
                                <label class="form-label">City</label>
                                <input type="text" id="city" name="city" class="form-control" placeholder="Select Pincode">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-4">
                                <label class="form-label">Full Address</label>
                                <textarea name="full_address" class="form-control" rows="2" placeholder="House no, Street, Landmark..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Qualification & KYC -->
                    <div class="section-title mt-4">
                        <i class="fas fa-id-card"></i> Qualification & KYC
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-4">
                                <label class="form-label">Higher Qualification</label>
                                <select name="qualification" class="form-select">
                                    <option value="">Select Qualification</option>
                                    <option value="5th">5th</option>
                                    <option value="8th">8th</option>
                                    <option value="10th">10th</option>
                                    <option value="12th">12th</option>
                                    <option value="Under Graduate">Under Graduate</option>
                                    <option value="Post Graduate">Post Graduate</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-4">
                                <label class="form-label">School/Board/University</label>
                                <input type="text" name="school_university" class="form-control" placeholder="e.g. CBSE / Delhi University">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-4">
                                <label class="form-label">Qualification Certificate</label>
                                <input type="file" name="qualification_cert" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-4">
                                <label class="form-label">Aadhar Number (12 Digits)</label>
                                <input type="text" id="aadhar_number" name="aadhar_number" class="form-control" placeholder="XXXX XXXX XXXX" maxlength="14" pattern="\d{4}\s\d{4}\s\d{4}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-4">
                                <label class="form-label">Upload Aadhar Card</label>
                                <input type="file" name="aadhar_card_file" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Software Access -->
                    <div class="section-title mt-4">
                        <i class="fas fa-laptop-code"></i> Software & Course Access
                    </div>
                    <div class="software-access-box">
                        <div class="row justify-content-center">
                            <div class="col-md-5 mb-4 mb-md-0">
                                <label class="form-label d-block text-center">Typing Master Access</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="typing_access" id="t_none" value="None" checked>
                                    <label class="btn btn-outline-secondary" for="t_none">None</label>
                                    
                                    <input type="radio" class="btn-check" name="typing_access" id="t_hindi" value="Hindi">
                                    <label class="btn btn-outline-success" for="t_hindi">Hindi</label>
                                    
                                    <input type="radio" class="btn-check" name="typing_access" id="t_english" value="English">
                                    <label class="btn btn-outline-success" for="t_english">English</label>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label d-block text-center">Steno Software Access</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="steno_access" id="s_none" value="None" checked>
                                    <label class="btn btn-outline-secondary" for="s_none">None</label>
                                    
                                    <input type="radio" class="btn-check" name="steno_access" id="s_hindi" value="Hindi">
                                    <label class="btn btn-outline-success" for="s_hindi">Hindi</label>
                                    
                                    <input type="radio" class="btn-check" name="steno_access" id="s_english" value="English">
                                    <label class="btn btn-outline-success" for="s_english">English</label>
                                </div>
                            </div>
                            <div class="col-md-10 mt-4 text-center">
                                <div class="custom-control custom-checkbox d-inline-block">
                                    <input class="custom-control-input" type="checkbox" id="punjabi_lms" name="punjabi_lms_access">
                                    <label for="punjabi_lms" class="custom-control-label font-weight-bold">Punjabi LMS Access</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-right mt-5">
                        <button type="reset" class="btn btn-light px-5 mr-2" style="border-radius:10px;">Clear Form</button>
                        <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm" style="border-radius:10px;">
                            <i class="fas fa-paper-plane mr-2"></i> Submit Admission
                        </button>
                    </div>
                </div>
            </div>
        </form>
        <div class="pb-5"></div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Image Preview
    function previewImg(input, target) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#' + target).attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    $(document).ready(function() {
        // Password Visibility Toggle
        $('.toggle-password').click(function() {
            const input = $(this).siblings('input');
            const icon = $(this).find('i');
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // Aadhar Formatting (0000 0000 0000)
        $('#aadhar_number').on('input', function() {
            let val = $(this).val().replace(/\D/g, '');
            let formatted = '';
            for(let i=0; i<val.length && i<12; i++) {
                if(i > 0 && i % 4 === 0) formatted += ' ';
                formatted += val[i];
            }
            $(this).val(formatted);
        });

        // Pincode API Integration
        $('#lookupPincode').click(function() {
            let pin = $('#pincode').val();
            if(pin.length !== 6) {
                Swal.fire('Error', 'Please enter a valid 6-digit pincode.', 'error');
                return;
            }

            $('#pincodeLoader').show();
            $.ajax({
                url: `https://api.postalpincode.in/pincode/${pin}`,
                type: 'GET',
                success: function(response) {
                    $('#pincodeLoader').hide();
                    if(response[0].Status === 'Success') {
                        let data = response[0].PostOffice[0];
                        $('#state').val(data.State);
                        $('#city').val(data.District);
                        $('#country').val('India');
                    } else {
                        Swal.fire('Not Found', 'Invalid pincode or data not available.', 'warning');
                    }
                },
                error: function() {
                    $('#pincodeLoader').hide();
                    Swal.fire('API Error', 'Failed to fetch address details. Please fill manually.', 'error');
                }
            });
        });

        // Form Submission
        $('#studentAdmissionForm').on('submit', function(e) {
            e.preventDefault();
            
            // Basic Frontend Validation
            if($('#password').val() !== $('#confirm_password').val()) {
                Swal.fire('Error', 'Passwords do not match!', 'error');
                return;
            }

            const formData = new FormData(this);
            formData.append('action', 'add_student');

            $('#loader-overlay').css('display', 'flex');

            $.ajax({
                url: 'student_action.php',
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
                            title: 'Admission Successful!',
                            text: response.message
                        }).then(() => {
                            window.location.href = 'manage-students.php';
                        });
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    $('#loader-overlay').hide();
                    Swal.fire('Error!', 'Something went wrong during submission.', 'error');
                }
            });
        });
    });
</script>

<?php include '../footer.php'; ?>
