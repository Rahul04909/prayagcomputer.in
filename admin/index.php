<?php include './header.php'; ?>
<?php
// Website Plan Calculation
$startDate = "2026-03-08";
$expiryDate = date('Y-m-d', strtotime($startDate . ' + 1 year'));
$currentDate = date('Y-m-d');

$diffTotal = date_diff(date_create($startDate), date_create($expiryDate))->days;
$diffRemaining = date_diff(date_create($currentDate), date_create($expiryDate))->days;
$diffPassed = $diffTotal - $diffRemaining;

$progressPercent = round(($diffPassed / $diffTotal) * 100);
if ($progressPercent > 100) $progressPercent = 100;
if ($progressPercent < 0) $progressPercent = 0;
?>

<style>
    .info-widget { border: none; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); background: white; margin-bottom: 25px; border-left: 5px solid #28a745; }
    .info-field { padding: 10px 15px; border-right: 1px solid #f1f1f1; }
    .info-field:last-child { border-right: none; }
    .info-label { font-size: 11px; text-transform: uppercase; color: #888; letter-spacing: 0.5px; margin-bottom: 3px; font-weight: 600; }
    .info-value { font-size: 14px; font-weight: 700; color: #2c3e50; }
    .progress-container { background: #f8f9fa; padding: 15px 20px; border-bottom: 1px solid #f1f1f1; }
    .days-badge { background: #e8f5e9; color: #2e7d32; font-weight: 800; border-radius: 20px; padding: 4px 12px; font-size: 13px; }
</style>

<!-- Website Plan Widget -->
<div class="card info-widget">
    <div class="progress-container">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h6 class="mb-0" style="font-weight:700; color:#343a40;">
                    <i class="fas fa-shield-alt text-success mr-2"></i> Website 1 Year Plan Duration
                </h6>
                <small class="text-muted">Started on: <strong><?= date('d M, Y', strtotime($startDate)) ?></strong> (Expires: <?= date('d M, Y', strtotime($expiryDate)) ?>)</small>
            </div>
            <div class="text-right">
                <span class="days-badge shadow-sm"><?= $diffRemaining ?> Days Pending</span>
            </div>
        </div>
        <div class="progress" style="height: 10px; border-radius: 10px;">
            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= $progressPercent ?>%" aria-valuenow="<?= $progressPercent ?>" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="row no-gutters text-center">
            <div class="col-md-2 col-6 info-field">
                <div class="info-label">Domain Info</div>
                <div class="info-value text-primary">prayagcomputer.in</div>
            </div>
            <div class="col-md-2 col-6 info-field">
                <div class="info-label">Registrar</div>
                <div class="info-value">Hostinger India</div>
            </div>
            <div class="col-md-2 col-6 info-field">
                <div class="info-label">Developer</div>
                <div class="info-value">Rahul Dhiman</div>
            </div>
            <div class="col-md-2 col-6 info-field">
                <div class="info-label">Dev Contact</div>
                <div class="info-value">+91-8059982049</div>
            </div>
            <div class="col-md-2 col-6 info-field">
                <div class="info-label">Website IP</div>
                <div class="info-value text-muted">148.113.35.192</div>
            </div>
            <div class="col-md-2 col-6 info-field">
                <div class="info-label">Server Hosting</div>
                <div class="info-value"><span class="badge badge-warning">Cpanel</span></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>150</h3>
                <p>New Orders</p>
            </div>
            <div class="icon">
                <i class="ion ion-bag"></i>
            </div>
            <a href="#" class="small-box-footer">More info
                <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>53<sup style="font-size: 20px">%</sup></h3>
                <p>Bounce Rate</p>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
            <a href="#" class="small-box-footer">More info
                <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>44</h3>
                <p>User Registrations</p>
            </div>
            <div class="icon">
                <i class="ion ion-person-add"></i>
            </div>
            <a href="#" class="small-box-footer">More info
                <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>65</h3>
                <p>Unique Visitors</p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <a href="#" class="small-box-footer">More info
                <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<?php include './footer.php'; ?>