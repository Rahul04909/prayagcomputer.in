<?php include './header.php'; ?>

<style>
    .profile-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.07);
        overflow: hidden;
        background: #fff;
    }
    .profile-card .card-header {
        background: linear-gradient(135deg, #28a745, #218838);
        color: #fff;
        padding: 20px 25px;
        font-weight: 600;
        border: none;
    }
    .profile-card .card-body { padding: 30px; }
    .profile-img-wrap {
        position: relative;
        width: 120px;
        height: 120px;
        margin: 0 auto 20px;
    }
    .profile-img-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        border: 4px solid #fff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.12);
    }
    .form-group label {
        font-weight: 600;
        color: #495057;
        font-size: 0.85rem;
        margin-bottom: 6px;
    }
    .form-control {
        border-radius: 8px;
        padding: 10px 14px;
        border: 1.5px solid #e9ecef;
        background: #f8f9fa;
        color: #495057;
    }
    .form-control[readonly] { cursor: default; opacity: 0.8; }
    .form-control:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40,167,69,0.12);
        background: #fff;
    }
    .info-label {
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        color: #adb5bd;
        margin-bottom: 4px;
    }
    .info-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: #343a40;
    }
    .section-divider {
        border-top: 1px dashed #e9ecef;
        margin: 30px 0;
    }
    .btn-action {
        border-radius: 8px;
        padding: 10px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-action:hover { transform: translateY(-2px); }
    .badge-info-pill {
        background: #e3f2fd;
        color: #1565c0;
        border-radius: 20px;
        padding: 4px 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .badge-success-pill {
        background: #e8f5e9;
        color: #2e7d32;
        border-radius: 20px;
        padding: 4px 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    #loader-overlay {
        position: fixed; top:0; left:0; width:100%; height:100%;
        background: rgba(255,255,255,0.8);
        display: none; align-items: center; justify-content: center; z-index: 9999;
    }
    .spinner-border-green { color: #28a745; }
    .pass-toggle { cursor: pointer; }
</style>

<div id="loader-overlay">
    <div class="spinner-border spinner-border-green" role="status" style="width:3rem;height:3rem;">
        <span class="sr-only">Loading...</span>
    </div>
</div>

<?php
// Fetch all student info with course details
$stmt = $pdo->prepare("SELECT s.*, c.title as course_title, c.duration as course_duration FROM students s LEFT JOIN courses c ON s.course_id = c.id WHERE s.id = ?");
$stmt->execute([$student['id']]);
$s = $stmt->fetch();

// Fee info
$fee_stmt = $pdo->prepare("SELECT SUM(amount_paid) as paid FROM student_fees WHERE student_id = ?");
$fee_stmt->execute([$student['id']]);
$fee_row = $fee_stmt->fetch();
$paid_fees = $fee_row['paid'] ?? 0;
$pending   = ($s['total_fees'] ?? 0) - $paid_fees;
?>

<section class="content">
  <div class="container-fluid py-3">
    <div class="row">

      <!-- Left: Profile Summary Card -->
      <div class="col-lg-4 mb-4">
        <div class="profile-card card h-100">
          <div class="card-header">
            <i class="fas fa-id-card mr-2"></i> My Profile
          </div>
          <div class="card-body text-center">
            <div class="profile-img-wrap">
              <img id="previewImg" src="<?= $student_image ?>" alt="Profile">
            </div>
            <h5 class="mb-1" style="font-weight:700;"><?= htmlspecialchars($s['student_name']) ?></h5>
            <span class="badge-info-pill"><?= htmlspecialchars($s['enrollment_no']) ?></span>
            <div class="mt-2">
              <span class="badge-success-pill"><?= htmlspecialchars($s['course_title'] ?? 'No Course') ?></span>
            </div>

            <hr class="my-3">

            <div class="text-left">
              <div class="row mb-3">
                <div class="col-6">
                  <p class="info-label">Mobile</p>
                  <p class="info-value"><?= htmlspecialchars($s['mobile']) ?></p>
                </div>
                <div class="col-6">
                  <p class="info-label">Email</p>
                  <p class="info-value text-truncate" title="<?= htmlspecialchars($s['email']) ?>"><?= htmlspecialchars($s['email'] ?: '—') ?></p>
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-6">
                  <p class="info-label">Father's Name</p>
                  <p class="info-value"><?= htmlspecialchars($s['father_name'] ?: '—') ?></p>
                </div>
                <div class="col-6">
                  <p class="info-label">Qualification</p>
                  <p class="info-value"><?= htmlspecialchars($s['qualification'] ?: '—') ?></p>
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-6">
                  <p class="info-label">State</p>
                  <p class="info-value"><?= htmlspecialchars($s['state'] ?: '—') ?></p>
                </div>
                <div class="col-6">
                  <p class="info-label">City</p>
                  <p class="info-value"><?= htmlspecialchars($s['city'] ?: '—') ?></p>
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-6">
                  <p class="info-label">Joined On</p>
                  <p class="info-value"><?= date('d M Y', strtotime($s['created_at'])) ?></p>
                </div>
                <div class="col-6">
                  <p class="info-label">Course Duration</p>
                  <p class="info-value"><?= htmlspecialchars($s['course_duration'] ?: '—') ?></p>
                </div>
              </div>
            </div>

            <hr class="my-3">

            <!-- Fee Summary -->
            <div class="row text-center">
              <div class="col-4">
                <p class="info-label">Total Fees</p>
                <p class="info-value text-dark">₹<?= number_format($s['total_fees'] ?? 0) ?></p>
              </div>
              <div class="col-4">
                <p class="info-label">Paid</p>
                <p class="info-value text-success">₹<?= number_format($paid_fees) ?></p>
              </div>
              <div class="col-4">
                <p class="info-label">Pending</p>
                <p class="info-value text-danger">₹<?= number_format($pending) ?></p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Change Password Card -->
      <div class="col-lg-8 mb-4">
        <div class="profile-card card">
          <div class="card-header">
            <i class="fas fa-shield-alt mr-2"></i> Change Password
          </div>
          <div class="card-body">
            <p class="text-muted mb-4" style="font-size:0.9rem;">
              <i class="fas fa-info-circle text-warning mr-1"></i>
              For your security, use a strong password that you don't use elsewhere.
            </p>

            <form id="passwordForm">
              <input type="hidden" name="action" value="change_password">

              <div class="form-group mb-4">
                <label>Current Password</label>
                <div class="input-group">
                  <input type="password" name="current_password" id="currentPass" class="form-control" placeholder="Enter your current password" required>
                  <div class="input-group-append">
                    <span class="input-group-text pass-toggle" onclick="togglePass('currentPass', this)">
                      <i class="fas fa-eye"></i>
                    </span>
                  </div>
                </div>
              </div>

              <div class="form-group mb-4">
                <label>New Password</label>
                <div class="input-group">
                  <input type="password" name="new_password" id="newPass" class="form-control" placeholder="Enter new password (min. 6 characters)" required>
                  <div class="input-group-append">
                    <span class="input-group-text pass-toggle" onclick="togglePass('newPass', this)">
                      <i class="fas fa-eye"></i>
                    </span>
                  </div>
                </div>
                <!-- Password Strength bar -->
                <div class="mt-2">
                  <div class="progress" style="height:5px; border-radius:4px;">
                    <div id="strengthBar" class="progress-bar" role="progressbar" style="width:0%;"></div>
                  </div>
                  <small id="strengthLabel" class="text-muted"></small>
                </div>
              </div>

              <div class="form-group mb-4">
                <label>Confirm New Password</label>
                <div class="input-group">
                  <input type="password" name="confirm_password" id="confirmPass" class="form-control" placeholder="Re-enter your new password" required>
                  <div class="input-group-append">
                    <span class="input-group-text pass-toggle" onclick="togglePass('confirmPass', this)">
                      <i class="fas fa-eye"></i>
                    </span>
                  </div>
                </div>
                <small id="matchMsg" class="mt-1 d-block"></small>
              </div>

              <div class="text-right">
                <button type="submit" class="btn btn-warning btn-action">
                  <i class="fas fa-key mr-2"></i> Update Password
                </button>
              </div>
            </form>

            <div class="section-divider"></div>

            <!-- Security Tips -->
            <h6 class="font-weight-bold mb-3"><i class="fas fa-lightbulb text-warning mr-2"></i> Password Tips</h6>
            <ul class="text-muted mb-0" style="font-size:0.87rem; line-height:1.8;">
              <li>Use at least 8 characters.</li>
              <li>Mix uppercase, lowercase, numbers, and symbols.</li>
              <li>Avoid using your name or enrollment number.</li>
              <li>Never share your password with anyone.</li>
            </ul>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<script>
// Toggle password visibility
function togglePass(inputId, el) {
    const input = document.getElementById(inputId);
    const icon = el.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Password strength meter
document.getElementById('newPass').addEventListener('input', function () {
    const val = this.value;
    let strength = 0;
    if (val.length >= 6)  strength++;
    if (val.length >= 10) strength++;
    if (/[A-Z]/.test(val)) strength++;
    if (/[0-9]/.test(val)) strength++;
    if (/[^A-Za-z0-9]/.test(val)) strength++;

    const bar   = document.getElementById('strengthBar');
    const label = document.getElementById('strengthLabel');
    const levels = [
        { pct: '20%', cls: 'bg-danger',  txt: 'Very Weak'  },
        { pct: '40%', cls: 'bg-warning', txt: 'Weak'       },
        { pct: '60%', cls: 'bg-info',    txt: 'Fair'       },
        { pct: '80%', cls: 'bg-primary', txt: 'Strong'     },
        { pct:'100%', cls: 'bg-success', txt: 'Very Strong'},
    ];
    if (val.length === 0) { bar.style.width = '0%'; label.textContent = ''; return; }
    const lvl = levels[Math.min(strength - 1, 4)];
    bar.style.width = lvl.pct;
    bar.className   = 'progress-bar ' + lvl.cls;
    label.textContent = lvl.txt;
    label.className   = 'text-muted mt-1 d-block';
});

// Password match check
document.getElementById('confirmPass').addEventListener('input', function () {
    const msg = document.getElementById('matchMsg');
    if (this.value === document.getElementById('newPass').value) {
        msg.textContent = '✅ Passwords match';
        msg.className   = 'text-success mt-1 d-block';
    } else {
        msg.textContent = '❌ Passwords do not match';
        msg.className   = 'text-danger mt-1 d-block';
    }
});

// Password form submit
$('#passwordForm').on('submit', function (e) {
    e.preventDefault();

    const newPass     = $('#newPass').val();
    const confirmPass = $('#confirmPass').val();

    if (newPass !== confirmPass) {
        Swal.fire('Error', 'New passwords do not match.', 'error');
        return;
    }
    if (newPass.length < 6) {
        Swal.fire('Error', 'Password must be at least 6 characters.', 'error');
        return;
    }

    $('#loader-overlay').css('display', 'flex');

    $.ajax({
        url: 'profile_action.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function (response) {
            $('#loader-overlay').hide();
            if (response.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Done!', text: response.message, confirmButtonColor: '#28a745' })
                    .then(() => { $('#passwordForm')[0].reset(); $('#strengthBar').css('width','0'); $('#strengthLabel').text(''); $('#matchMsg').text(''); });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: response.message });
            }
        },
        error: function () {
            $('#loader-overlay').hide();
            Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
        }
    });
});
</script>

<?php include './footer.php'; ?>