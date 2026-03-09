/**
 * Shopee HR - Recruitment Module
 * Handles applicant CRUD and filtering
 */
const ApplicantManager = {
    applicants: JSON.parse(localStorage.getItem('shoppe_applicants')) || [
        { id: 1, firstName: 'Robert', lastName: 'Johnson', position: 'Senior Software Engineer', stage: 'Final Interview', interviewDate: '2026-03-05', appliedDate: '2026-02-15' },
        { id: 2, firstName: 'Lisa', lastName: 'Wong', position: 'Marketing Manager', stage: 'Interview', interviewDate: '2026-03-03', appliedDate: '2026-02-20' },
        { id: 3, firstName: 'Carlos', lastName: 'Mendez', position: 'HR Specialist', stage: 'Screening', interviewDate: '', appliedDate: '2026-02-25' }
    ],

    init() {
        this.render();
        this.updateSummary();

        const form = document.getElementById('applicantForm');
        if (form) {
            form.addEventListener('submit', (e) => this.handleSubmit(e));
        }

        const searchFilter = document.getElementById('searchFilter');
        if (searchFilter) {
            searchFilter.addEventListener('input', () => this.filter());
        }
    },

    save() {
        localStorage.setItem('shoppe_applicants', JSON.stringify(this.applicants));
        this.updateSummary();
    },

    render(data = this.applicants) {
        const tbody = document.getElementById('applicantTableBody');
        if (!tbody) return;

        tbody.innerHTML = '';
        data.forEach(app => {
            const tr = document.createElement('tr');
            const stageClass = this.getStageClass(app.stage);
            tr.innerHTML = `
        <td>${app.firstName} ${app.lastName}</td>
        <td>${app.position}</td>
        <td>${app.stage}</td>
        <td>${app.interviewDate || '-'}</td>
        <td>${app.appliedDate}</td>
        <td><span class="status-badge ${stageClass}">${app.stage}</span></td>
        <td>
          <button class="btn-action btn-view" onclick="ApplicantManager.view(${app.id})">👁</button>
          <button class="btn-action btn-edit" onclick="ApplicantManager.edit(${app.id})">✎</button>
          <button class="btn-action btn-delete" onclick="ApplicantManager.delete(${app.id})">🗑</button>
        </td>
      `;
            tbody.appendChild(tr);
        });
    },

    getStageClass(stage) {
        switch (stage) {
            case 'Hired': return 'status-active';
            case 'Rejected': return 'status-rejected';
            case 'Applied': return 'status-applied';
            case 'Screening': return 'status-screening';
            case 'Interview': return 'status-interview';
            case 'Final Interview': return 'status-final-interview';
            default: return 'status-applied';
        }
    },

    updateSummary() {
        const cards = document.querySelectorAll('.card .number');
        if (cards.length >= 4) {
            cards[1].textContent = this.applicants.length; // Total Applicants
            cards[2].textContent = this.applicants.filter(a => a.stage.includes('Interview')).length; // Interview Scheduled
        }
    },

    openModal(app = null) {
        const modal = document.getElementById('applicantModal');
        const overlay = document.querySelector('.overlay');
        const form = document.getElementById('applicantForm');
        const title = document.getElementById('modalTitle');

        if (!modal || !overlay || !form) return;

        form.reset();
        document.getElementById('applicantId').value = '';
        title.textContent = 'Add New Applicant';

        if (app) {
            title.textContent = 'Edit Applicant';
            document.getElementById('applicantId').value = app.id;
            document.getElementById('firstName').value = app.firstName;
            document.getElementById('lastName').value = app.lastName;
            document.getElementById('applicantPosition').value = app.position;
            document.getElementById('applicantStage').value = app.stage;
            document.getElementById('interviewDate').value = app.interviewDate;
            document.getElementById('appliedDate').value = app.appliedDate;
        }

        modal.style.display = 'block';
        overlay.style.display = 'block';
    },

    closeModal() {
        const modal = document.getElementById('applicantModal');
        const overlay = document.querySelector('.overlay');
        if (modal) modal.style.display = 'none';
        if (overlay) overlay.style.display = 'none';
    },

    handleSubmit(e) {
        e.preventDefault();
        const id = document.getElementById('applicantId').value;
        const appData = {
            firstName: document.getElementById('firstName').value,
            lastName: document.getElementById('lastName').value,
            position: document.getElementById('applicantPosition').value,
            stage: document.getElementById('applicantStage').value,
            interviewDate: document.getElementById('interviewDate').value,
            appliedDate: document.getElementById('appliedDate').value
        };

        if (id) {
            const index = this.applicants.findIndex(a => a.id == id);
            this.applicants[index] = { ...this.applicants[index], ...appData };
        } else {
            appData.id = Date.now();
            this.applicants.push(appData);
        }

        this.save();
        this.render();
        this.closeModal();
    },

    delete(id) {
        if (confirm('Are you sure you want to delete this applicant?')) {
            this.applicants = this.applicants.filter(a => a.id !== id);
            this.save();
            this.render();
        }
    },

    edit(id) {
        const app = this.applicants.find(a => a.id === id);
        this.openModal(app);
    },

    view(id) {
        const app = this.applicants.find(a => a.id === id);
        alert(`Applicant Details:\n\nName: ${app.firstName} ${app.lastName}\nPosition: ${app.position}\nStage: ${app.stage}\nApplied: ${app.appliedDate}`);
    },

    filter() {
        const search = document.getElementById('searchFilter').value.toLowerCase();
        const position = document.getElementById('position').value;
        const stage = document.getElementById('stage').value;

        const filtered = this.applicants.filter(app => {
            const matchesSearch = (app.firstName + ' ' + app.lastName).toLowerCase().includes(search);
            const matchesPosition = position === 'All Positions' || app.position === position;
            const matchesStage = stage === 'All Stages' || app.stage === stage;
            return matchesSearch && matchesPosition && matchesStage;
        });

        this.render(filtered);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('applicantTableBody')) {
        ApplicantManager.init();
    }
});
