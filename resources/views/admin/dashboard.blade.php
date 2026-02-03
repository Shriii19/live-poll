@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="row">
    <div class="col-12 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h3><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h3>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createPollModal">
                <i class="fas fa-plus me-2"></i>Create Poll
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-list me-2"></i>All Polls
            </div>
            <div class="card-body p-0">
                <div id="admin-polls-list" class="list-group list-group-flush">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div id="poll-voters-panel" class="card" style="display: none;">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-users me-2"></i>Voters for: <span id="admin-poll-question"></span>
            </div>
            <div class="card-body">
                <div id="voters-list">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                    </div>
                </div>
            </div>
        </div>

        <div id="no-poll-selected-admin" class="text-center py-5">
            <i class="fas fa-hand-pointer fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">Select a poll to manage voters</h5>
        </div>
    </div>
</div>

<!-- Create Poll Modal -->
<div class="modal fade" id="createPollModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Create New Poll</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createPollForm">
                    <div class="mb-3">
                        <label class="form-label">Question</label>
                        <input type="text" class="form-control" id="poll-question-input" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Options</label>
                        <div id="options-container">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control option-input" placeholder="Option 1" required>
                            </div>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control option-input" placeholder="Option 2" required>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addOption()">
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-history me-2"></i>Vote History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">IP: <code id="history-ip"></code></p>
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
            $list.html('<div class="text-center py-4 text-muted">No polls created yet</div>');
            return;
        }

        let html = '';
        polls.forEach(poll => {
            const activeClass = poll.id === adminCurrentPollId ? 'active' : '';
            const statusBadge = poll.status === 'active' 
                ? '<span class="badge bg-success">Active</span>' 
                : '<span class="badge bg-secondary">Inactive</span>';
            
            html += `
                <a href="#" class="list-group-item list-group-item-action ${activeClass}" 
                   data-poll-id="${poll.id}" onclick="loadPollVoters(${poll.id}); return false;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-poll-h me-2"></i>${poll.question}</span>
                        ${statusBadge}
                    </div>
                    <small class="text-muted">${poll.active_votes_count || 0} votes</small>
                </a>
            `;
        });
        $list.html(html);
    }

    function loadPollVoters(pollId) {
        adminCurrentPollId = pollId;

        $('#admin-polls-list .list-group-item').removeClass('active');
        $(`#admin-polls-list [data-poll-id="${pollId}"]`).addClass('active');

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
            $list.html('<div class="text-center py-4 text-muted">No votes recorded yet</div>');
            return;
        }

        let html = '<div class="table-responsive"><table class="table table-hover">';
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

        voters.forEach(voter => {
            const hasActiveVote = voter.current_vote !== null;
            const statusBadge = hasActiveVote
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-secondary">Released</span>';
            
            const currentVote = hasActiveVote 
                ? voter.current_vote.option_text 
                : '-';

            const historyCount = voter.history.length;
            const hasMultipleVotes = historyCount > 1;
            
            html += `
                <tr>
                    <td><code>${voter.ip_address}</code></td>
                    <td>${currentVote}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-info" onclick="viewHistory(${pollId}, '${voter.ip_address}')">
                            <i class="fas fa-history me-1"></i>${historyCount} ${hasMultipleVotes ? 'entries' : 'entry'}
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
                    showAlert('IP released successfully. Vote removed.', 'success');
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
