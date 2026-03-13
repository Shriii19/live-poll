@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('styles')
<style>
    .admin-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .admin-title {
        font-size: 1.75rem;
        font-weight: 800;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .admin-title i {
        -webkit-text-fill-color: initial;
        color: #6366f1;
    }

    .stat-card {
        background: var(--glass-bg);
        border-radius: 16px;
        padding: 1.25rem;
        border: 1px solid var(--glass-border);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--card-hover-shadow);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: white;
    }

    .stat-icon.primary { background: var(--primary-gradient); }
    .stat-icon.success { background: var(--success-gradient); }
    .stat-icon.warning { background: var(--warning-gradient); }

    .poll-admin-item {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
    }

    .poll-admin-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(135deg, #2c3e50, #34495e);
        transform: scaleY(0);
        transition: transform 0.3s ease;
    }

    .poll-admin-item:hover::before,
    .poll-admin-item.active::before {
        transform: scaleY(1);
    }

    .poll-admin-item:hover {
        background: rgba(44, 62, 80, 0.05);
        padding-left: 1.5rem;
    }

    .poll-admin-item.active {
        background: rgba(44, 62, 80, 0.1);
        padding-left: 1.5rem;
    }

    .voter-table {
        border-radius: 16px;
        overflow: hidden;
    }

    .voter-table thead {
        background: linear-gradient(135deg, #2c3e50, #34495e);
    }

    .voter-row {
        transition: all 0.3s ease;
    }

    .voter-row:hover {
        background: rgba(44, 62, 80, 0.05);
    }

    .ip-badge {
        background: rgba(99, 102, 241, 0.1);
        color: #6366f1;
        padding: 6px 12px;
        border-radius: 8px;
        font-family: 'Courier New', monospace;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .admin-card-header {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%) !important;
    }

    .empty-admin-state {
        padding: 3rem 2rem;
        text-align: center;
    }

    .empty-admin-state i {
        font-size: 4rem;
        color: #334155;
        opacity: 0.5;
        margin-bottom: 1rem;
    }

    .poll-vote-count {
        background: rgba(16, 185, 129, 0.15);
        color: #10b981;
        padding: 4px 10px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
    }
</style>
@endsection

@section('content')
<div class="admin-header" style="animation: fadeIn 0.5s ease;">
    <h3 class="admin-title mb-0">
        <i class="fas fa-tachometer-alt"></i>
        Admin Dashboard
    </h3>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createPollModal">
        <i class="fas fa-plus me-2"></i>Create Poll
    </button>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card" style="animation: fadeIn 0.5s ease;">
            <div class="card-header admin-card-header d-flex align-items-center justify-content-between">
                <span><i class="fas fa-list me-2"></i>All Polls</span>
                <span class="live-indicator">LIVE</span>
            </div>
            <div class="card-body p-0" style="max-height: 70vh; overflow-y: auto;">
                <div id="admin-polls-list">
                    <div class="empty-admin-state">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p class="text-muted mb-0">Loading polls...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div id="poll-voters-panel" class="card" style="display: none; animation: fadeIn 0.5s ease;">
            <div class="card-header admin-card-header d-flex align-items-center justify-content-between">
                <span class="d-flex align-items-center gap-2">
                    <i class="fas fa-users me-2"></i>
                    Voters for: <strong class="ms-1" id="admin-poll-question"></strong>
                </span>
                <span class="live-indicator">LIVE</span>
            </div>
            <div class="card-body">
                <div id="voters-list">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                    </div>
                </div>
            </div>
        </div>

        <div id="no-poll-selected-admin" class="text-center py-5" style="animation: fadeIn 0.5s ease;">
            <div class="empty-admin-state">
                <i class="fas fa-hand-pointer"></i>
                <h5 style="color: #334155;">Select a poll to manage</h5>
                <p class="text-muted mb-0">Choose a poll to view voters and history</p>
            </div>
        </div>
    </div>
</div>

<!-- Create Poll Modal -->
<div class="modal fade" id="createPollModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Create New Poll</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createPollForm">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Question</label>
                        <input type="text" class="form-control" id="poll-question-input" placeholder="What would you like to ask?" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Options</label>
                        <div id="options-container">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control option-input" placeholder="Option 1" required>
                            </div>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control option-input" placeholder="Option 2" required>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addOption()">
                            <i class="fas fa-plus me-1"></i>Add Option
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createPoll()">
                    <i class="fas fa-save me-1"></i>Create Poll
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Vote History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-history me-2"></i>Vote History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">
                    <span class="text-muted">IP Address:</span> 
                    <span class="ip-badge" id="history-ip"></span>
                </p>
                <div id="history-timeline" class="history-timeline"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let adminCurrentPollId = null;
    let votersInterval = null;

    function loadAdminPolls() {
        $.get('/api/admin/polls', function(response) {
            if (response.success) {
                renderAdminPollsList(response.polls);
            }
        });
    }

    function renderAdminPollsList(polls) {
        const $list = $('#admin-polls-list');
        
        if (polls.length === 0) {
            $list.html(`
                <div class="empty-admin-state">
                    <i class="fas fa-inbox"></i>
                    <p class="text-muted mb-0">No polls created yet</p>
                </div>
            `);
            return;
        }

        let html = '';
        polls.forEach((poll, index) => {
            const activeClass = poll.id === adminCurrentPollId ? 'active' : '';
            const statusBadge = poll.status === 'active' 
                ? '<span class="badge bg-success">Active</span>' 
                : '<span class="badge bg-secondary">Inactive</span>';
            
            html += `
                <div class="poll-admin-item ${activeClass}" 
                     data-poll-id="${poll.id}" 
                     onclick="loadPollVoters(${poll.id})"
                     style="animation: fadeIn ${0.3 + index * 0.1}s ease;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-semibold"><i class="fas fa-poll-h me-2 text-muted"></i>${poll.question}</span>
                        ${statusBadge}
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="poll-vote-count">
                            <i class="fas fa-users me-1"></i>${poll.active_votes_count || 0} votes
                        </span>
                    </div>
                </div>
            `;
        });
        $list.html(html);
    }

    function loadPollVoters(pollId) {
        adminCurrentPollId = pollId;

        $('.poll-admin-item').removeClass('active');
        $(`.poll-admin-item[data-poll-id="${pollId}"]`).addClass('active');

        $('#no-poll-selected-admin').hide();
        $('#poll-voters-panel').show();
        
        fetchVoters(pollId);
        startVotersPolling();
    }

    function fetchVoters(pollId) {
        $.get(`/api/admin/polls/${pollId}/voters-history`, function(response) {
            if (response.success) {
                $('#admin-poll-question').text(response.poll.question);
                renderVotersList(response.voters, pollId);
            }
        });
    }

    function renderVotersList(voters, pollId) {
        const $list = $('#voters-list');
        
        if (voters.length === 0) {
            $list.html(`
                <div class="empty-admin-state">
                    <i class="fas fa-vote-yea"></i>
                    <p class="text-muted mb-0">No votes recorded yet</p>
                </div>
            `);
            return;
        }

        let html = '<div class="table-responsive"><table class="table table-hover voter-table mb-0">';
        html += `
            <thead>
                <tr>
                    <th>IP Address</th>
                    <th>Current Vote</th>
                    <th>Status</th>
                    <th>History</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
        `;

        voters.forEach((voter, index) => {
            const hasActiveVote = voter.current_vote !== null;
            const statusBadge = hasActiveVote
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-secondary">Released</span>';
            
            const currentVote = hasActiveVote 
                ? voter.current_vote.option_text 
                : '<span class="text-muted">-</span>';

            const historyCount = voter.history.length;
            const hasMultipleVotes = historyCount > 1;
            
            html += `
                <tr class="voter-row" style="animation: fadeIn ${0.2 + index * 0.05}s ease;">
                    <td><span class="ip-badge">${voter.ip_address}</span></td>
                    <td class="fw-medium">${currentVote}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="viewHistory(${pollId}, '${voter.ip_address}')">
                            <i class="fas fa-history me-1"></i>${historyCount}
                        </button>
                    </td>
                    <td>
                        ${hasActiveVote ? `
                            <button class="btn btn-sm btn-danger" onclick="releaseIP(${pollId}, '${voter.ip_address}')">
                                <i class="fas fa-unlock me-1"></i>Release
                            </button>
                        ` : '<span class="text-muted">-</span>'}
                    </td>
                </tr>
            `;
        });

        html += '</tbody></table></div>';
        $list.html(html);
    }

    function releaseIP(pollId, ip) {
        if (!confirm(`Release IP ${ip}? This will remove their vote.`)) return;

        $.ajax({
            url: '/api/admin/release-ip',
            method: 'POST',
            data: {
                poll_id: pollId,
                ip_address: ip
            },
            success: function(response) {
                if (response.success) {
                    showAlert('IP released successfully!', 'success');
                    fetchVoters(pollId);
                    loadAdminPolls();
                }
            },
            error: function(xhr) {
                showAlert(xhr.responseJSON?.message || 'Failed to release IP', 'danger');
            }
        });
    }

    function viewHistory(pollId, ip) {
        $('#history-ip').text(ip);
        
        $.get(`/api/admin/polls/${pollId}/history/${encodeURIComponent(ip)}`, function(response) {
            if (response.success) {
                renderHistoryTimeline(response.history);
                $('#historyModal').modal('show');
            }
        });
    }

    function renderHistoryTimeline(history) {
        let html = '';
        
        history.forEach(item => {
            const isReleased = item.action === 'released';
            const icon = isReleased ? 'fa-times-circle text-danger' : 'fa-check-circle text-success';
            const action = isReleased ? 'Vote Released' : `Voted: ${item.option_text}`;
            const date = new Date(item.created_at).toLocaleString();
            
            html += `
                <div class="history-item ${isReleased ? 'released' : ''}">
                    <div class="d-flex justify-content-between">
                        <strong><i class="fas ${icon} me-2"></i>${action}</strong>
                        <small class="text-muted">${date}</small>
                    </div>
                </div>
            `;
        });
        
        $('#history-timeline').html(html);
    }

    function startVotersPolling() {
        if (votersInterval) clearInterval(votersInterval);
        
        votersInterval = setInterval(function() {
            if (adminCurrentPollId) {
                fetchVoters(adminCurrentPollId);
            }
        }, 1000);
    }

    function addOption() {
        const count = $('.option-input').length + 1;
        $('#options-container').append(`
            <div class="input-group mb-2">
                <input type="text" class="form-control option-input" placeholder="Option ${count}" required>
                <button type="button" class="btn btn-outline-danger" onclick="$(this).parent().remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `);
    }

    function createPoll() {
        const question = $('#poll-question-input').val().trim();
        const options = [];
        
        $('.option-input').each(function() {
            const val = $(this).val().trim();
            if (val) options.push(val);
        });

        if (!question || options.length < 2) {
            showAlert('Please enter a question and at least 2 options', 'warning');
            return;
        }

        $.ajax({
            url: '/api/polls',
            method: 'POST',
            data: { question, options },
            success: function(response) {
                if (response.success) {
                    showAlert('Poll created successfully!', 'success');
                    $('#createPollModal').modal('hide');
                    $('#createPollForm')[0].reset();
                    loadAdminPolls();
                }
            },
            error: function(xhr) {
                showAlert(xhr.responseJSON?.message || 'Failed to create poll', 'danger');
            }
        });
    }

    $(document).ready(function() {
        loadAdminPolls();
        setInterval(loadAdminPolls, 5000);
    });
</script>
@endsection
