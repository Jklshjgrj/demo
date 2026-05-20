<?php
/**
 * Live Map Module
 * FixIt - Community Infrastructure Reports
 */

require_once '../includes/auth.php';
require_once '../includes/db.php';

$page_title = 'Explore Map';
$body_class = 'bg-body';
require_once '../includes/header.php';
?>

<div class="row g-0 rounded-4 overflow-hidden border border-white border-opacity-10 shadow-lg" style="height: 75vh;">
    <!-- Map Filter Column -->
    <div class="col-lg-3 bg-white bg-opacity-5 border-end border-white border-opacity-10 d-flex flex-column h-100">
        <div class="p-4 flex-grow-1 overflow-y-auto">
            <div class="d-flex align-items-center gap-2 mb-4">
                <i data-lucide="sliders" class="text-primary" style="width: 20px;"></i>
                <h6 class="fw-bold mb-0 text-uppercase letter-spacing-05 small">Map Filters</h6>
            </div>

            <form id="filter-form">
                <div class="mb-4">
                    <label class="fixit-label">Issue Type</label>
                    <select name="issue_type" class="fixit-input">
                        <option value="">All Categories</option>
                        <option value="Damaged Road">Damaged Road</option>
                        <option value="Broken Streetlight">Broken Streetlight</option>
                        <option value="Drainage Issue">Drainage Issue</option>
                        <option value="Illegal Dumping">Illegal Dumping</option>
                        <option value="Water Leak">Water Leak</option>
                        <option value="Power Outage">Power Outage</option>
                        <option value="Pothole">Pothole</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="fixit-label">Status</label>
                    <div class="d-flex flex-wrap gap-2">
                         <input type="radio" class="btn-check" name="status" id="status-all" value="" checked>
                         <label class="btn btn-outline-secondary btn-sm rounded-pill px-3" style="font-size: 0.7rem;" for="status-all">All</label>
                         
                         <input type="radio" class="btn-check" name="status" id="status-open" value="Open">
                         <label class="btn btn-outline-danger btn-sm rounded-pill px-3" style="font-size: 0.7rem;" for="status-open">Open</label>
                         
                         <input type="radio" class="btn-check" name="status" id="status-resolved" value="Resolved">
                         <label class="btn btn-outline-success btn-sm rounded-pill px-3" style="font-size: 0.7rem;" for="status-resolved">Resolved</label>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="fixit-label">Date Range</label>
                    <div class="d-flex flex-column gap-2">
                        <input type="date" name="date_from" class="fixit-input py-2 small" style="font-size: 0.8rem;">
                        <input type="date" name="date_to" class="fixit-input py-2 small" style="font-size: 0.8rem;">
                    </div>
                </div>

                <button type="submit" class="btn-fixit btn-fixit-primary w-100 mb-2 py-2">Update Map</button>
                <button type="reset" id="btn-reset" class="btn-fixit btn-fixit-outline w-100 py-2">Reset</button>
            </form>
        </div>
        
        <div class="p-4 border-top border-white border-opacity-10 bg-white bg-opacity-5">
            <h6 class="fw-bold mb-3 small text-muted text-uppercase letter-spacing-05">Live Statistics</h6>
            <div id="stats-container" class="d-flex flex-column gap-2">
                <div class="text-muted small italic">Processing markers...</div>
            </div>
        </div>
    </div>

    <!-- Map View Column -->
    <div class="col-lg-9 position-relative">
        <div id="live-map" class="w-100 h-100"></div>
        
        <!-- Legend Overlay -->
        <div class="position-absolute bottom-0 end-0 m-4 p-3 bg-dark bg-opacity-75 rounded-4 border border-white border-opacity-10 shadow-lg" style="z-index: 900; backdrop-filter: blur(8px);">
            <div class="d-flex flex-column gap-2">
                <div class="d-flex align-items-center gap-2 small"><span class="rounded-circle" style="width: 10px; height: 10px; background: #EF4444;"></span> <span class="text-white opacity-75">Open Issue</span></div>
                <div class="d-flex align-items-center gap-2 small"><span class="rounded-circle" style="width: 10px; height: 10px; background: #3B82F6;"></span> <span class="text-white opacity-75">In Progress</span></div>
                <div class="d-flex align-items-center gap-2 small"><span class="rounded-circle" style="width: 10px; height: 10px; background: #22C55E;"></span> <span class="text-white opacity-75">Resolved</span></div>
            </div>
        </div>
    </div>
</div>

<?php 
$extra_scripts = '<script src="../assets/js/map_live.js"></script>';
require_once '../includes/footer.php'; 
?>
