/**
 * ProfileLogic - Handles PDS Resume Upload and Leave History Data
 */
const ProfileLogic = {
    // Current user context
    currentUser: JSON.parse(localStorage.getItem('currentUser')) || { username: 'guest' },

    /**
     * Initialize PDS Page
     */
    initPDS: function () {
        const resumeInput = document.getElementById('resumeInput');
        const uploadZone = document.getElementById('uploadZone');

        if (!resumeInput || !uploadZone) return;

        // Load saved resume
        this.loadSavedResume();

        // Handle File Selection
        resumeInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                this.handleFileUpload(file);
            }
        });

        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            }, false);
        });

        // Highlight drop zone
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadZone.addEventListener(eventName, () => {
                uploadZone.classList.add('highlight');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadZone.addEventListener(eventName, () => {
                uploadZone.classList.remove('highlight');
            }, false);
        });

        // Handle dropped files
        uploadZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const file = dt.files[0];
            if (file) {
                this.handleFileUpload(file);
            }
        });
    },

    /**
     * Handle File Upload & Conversion to Base64
     */
    handleFileUpload: function (file) {
        if (file.size > 5 * 1024 * 1024) {
            alert('File is too large. Max 5MB allowed.');
            return;
        }

        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = () => {
            const fileData = {
                name: file.name,
                size: (file.size / (1024 * 1024)).toFixed(2) + ' MB',
                type: file.type,
                data: reader.result
            };

            // Save to LocalStorage
            localStorage.setItem(`resume_${this.currentUser.username}`, JSON.stringify(fileData));
            this.showPreview(fileData);
        };
    },

    /**
     * Load Saved Resume from LocalStorage
     */
    loadSavedResume: function () {
        const savedData = localStorage.getItem(`resume_${this.currentUser.username}`);
        if (savedData) {
            this.showPreview(JSON.parse(savedData));
        }
    },

    /**
     * Show File Preview
     */
    showPreview: function (fileData) {
        document.getElementById('uploadZone').style.display = 'none';
        document.getElementById('filePreviewArea').style.display = 'block';
        document.getElementById('fileName').textContent = fileData.name;
        document.getElementById('fileSize').textContent = `(${fileData.size})`;

        const previewEmbed = document.getElementById('previewEmbed');
        previewEmbed.innerHTML = '';

        if (fileData.type === 'application/pdf') {
            const embed = document.createElement('embed');
            embed.src = fileData.data;
            embed.type = 'application/pdf';
            embed.className = 'preview-embed';
            previewEmbed.appendChild(embed);
        } else if (fileData.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.src = fileData.data;
            img.className = 'pds-preview-image';
            previewEmbed.appendChild(img);
        }
    },

    /**
     * Remove Resume
     */
    clearResume: function () {
        if (confirm('Are you sure you want to remove this file?')) {
            localStorage.removeItem(`resume_${this.currentUser.username}`);
            document.getElementById('uploadZone').style.display = 'block';
            document.getElementById('filePreviewArea').style.display = 'none';
            document.getElementById('resumeInput').value = '';
        }
    },

    /**
     * Initialize Leave History Page
     */
    initLeaveHistory: function () {
        this.renderLeaveTable();
    },

    /**
     * Render Leave History Table
     */
    renderLeaveTable: function () {
        const tableBody = document.getElementById('leaveHistoryTableBody');
        if (!tableBody) return;

        // Default mock data
        const defaultLeaves = [
            { id: 'mock1', type: 'Vacation Leave', start: 'Feb 10', end: 'Feb 12, 2026', days: '3', status: 'Approved', editable: false },
            { id: 'mock2', type: 'Sick Leave', start: 'Jan 05, 2026', end: 'Jan 05, 2026', days: '1', status: 'Approved', editable: false }
        ];

        // Load user data
        const userData = JSON.parse(localStorage.getItem(`leaves_${this.currentUser.username}`)) || [];
        const allLeaves = [...userData, ...defaultLeaves];

        tableBody.innerHTML = allLeaves.map(leave => `
            <tr>
                <td style="font-weight: 500;">${leave.type}</td>
                <td>${leave.start}${leave.end && leave.start !== leave.end ? ' - ' + leave.end : ''}</td>
                <td>${leave.days}</td>
                <td><span class="status-pill ${leave.status.toLowerCase()}">${leave.status}</span></td>
                <td style="text-align: right;">
                    ${leave.status === 'Pending' ? `
                        <div class="action-buttons">
                            <button class="btn-action btn-edit" title="Edit" onclick="ProfileLogic.editLeave('${leave.id}')">✏️</button>
                            <button class="btn-action btn-delete" title="Delete" onclick="ProfileLogic.deleteLeave('${leave.id}')">🗑️</button>
                        </div>
                    ` : '<span class="action-placeholder">Processed</span>'}
                </td>
            </tr>
        `).join('');
    },

    /**
     * Modal Handling
     */
    openLeaveModal: function (leaveId = null) {
        const modal = document.getElementById('leaveModalOverlay');
        if (!modal) return;

        const form = document.getElementById('leaveRequestForm');
        const title = document.getElementById('modalTitle');

        form.reset();
        document.getElementById('editLeaveId').value = '';
        if (title) title.textContent = 'Request Leave';

        if (leaveId) {
            const userData = JSON.parse(localStorage.getItem(`leaves_${this.currentUser.username}`)) || [];
            const leave = userData.find(l => l.id === leaveId);
            if (leave) {
                document.getElementById('editLeaveId').value = leave.id;
                document.getElementById('leaveType').value = leave.type;
                document.getElementById('startDate').value = leave.startDateRaw;
                document.getElementById('endDate').value = leave.endDateRaw;
                document.getElementById('leaveReason').value = leave.reason;
                document.getElementById('leaveDays').value = leave.days;
                if (title) title.textContent = 'Edit Leave Request';
            }
        }

        modal.style.display = 'flex';
    },

    closeLeaveModal: function () {
        const modal = document.getElementById('leaveModalOverlay');
        if (modal) modal.style.display = 'none';
    },

    /**
     * Save Leave Request
     */
    saveLeaveRequest: function (event) {
        event.preventDefault();

        const editId = document.getElementById('editLeaveId').value;
        const type = document.getElementById('leaveType').value;
        const start = document.getElementById('startDate').value;
        const end = document.getElementById('endDate').value;
        const reason = document.getElementById('leaveReason').value;
        const days = document.getElementById('leaveDays').value;

        // Simple date formatting
        const formatDate = (dateStr) => {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
        };

        const leaveData = {
            id: editId || 'leave_' + Date.now(),
            type,
            start: formatDate(start),
            end: formatDate(end),
            startDateRaw: start,
            endDateRaw: end,
            days,
            reason,
            status: 'Pending',
            editable: true
        };

        let userData = JSON.parse(localStorage.getItem(`leaves_${this.currentUser.username}`)) || [];

        if (editId) {
            userData = userData.map(l => l.id === editId ? leaveData : l);
        } else {
            userData.unshift(leaveData);
        }

        localStorage.setItem(`leaves_${this.currentUser.username}`, JSON.stringify(userData));
        this.closeLeaveModal();
        this.renderLeaveTable();
    },

    /**
     * Delete Leave
     */
    deleteLeave: function (id) {
        if (confirm('Are you sure you want to cancel this leave request?')) {
            let userData = JSON.parse(localStorage.getItem(`leaves_${this.currentUser.username}`)) || [];
            userData = userData.filter(l => l.id !== id);
            localStorage.setItem(`leaves_${this.currentUser.username}`, JSON.stringify(userData));
            this.renderLeaveTable();
        }
    },

    /**
     * Edit Leave
     */
    editLeave: function (id) {
        this.openLeaveModal(id);
    },

    /**
     * Calculate Days
     */
    calculateDays: function () {
        const startInput = document.getElementById('startDate').value;
        const endInput = document.getElementById('endDate').value;

        if (startInput && endInput) {
            const start = new Date(startInput);
            const end = new Date(endInput);
            if (end >= start) {
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                document.getElementById('leaveDays').value = diffDays;
            } else {
                document.getElementById('leaveDays').value = 0;
            }
        }
    }
};
