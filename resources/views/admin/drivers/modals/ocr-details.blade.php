<!-- OCR Details Modal -->
<div class="modal fade" id="ocrDetailsModal" tabindex="-1" aria-labelledby="ocrDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="ocrDetailsModalLabel">
                    <i class="fas fa-search"></i> OCR Verification Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="ocrDetailsContent">
                <!-- Content will be loaded dynamically -->
                <div class="text-center py-5">
                    <div class="processing-spinner"></div>
                    <div class="mt-2">Loading OCR details...</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
                <button type="button" class="btn btn-primary" onclick="reprocessOCR()" id="reprocessBtn">
                    <i class="fas fa-redo"></i> Reprocess OCR
                </button>
                <button type="button" class="btn btn-warning" onclick="showManualOverride()" id="overrideBtn">
                    <i class="fas fa-user-cog"></i> Manual Override
                </button>
            </div>
        </div>
    </div>
</div>