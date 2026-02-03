<?php
require_once __DIR__ . '/../../config.php';

if (!isLoggedIn()) {
    redirect('/login');
}

if (!isAdmin()) {
    redirect('/polls');
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Live Poll Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand { font-weight: bold; }
        .card { border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
        .alert-floating { position: fixed; top: 80px; right: 20px; z-index: 1050; min-width: 300px; }
        .history-timeline { border-left: 3px solid #0d6efd; padding-left: 20px; }
        .history-item { position: relative; padding-bottom: 15px; }
        .history-item::before { content: ''; position: absolute; left: -26px; top: 5px; width: 12px; height: 12px; border-radius: 50%; background: #0d6efd; }
        .history-item.released::before { background: #dc3545; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/polls">
                <i class="fas fa-poll me-2"></i>Live Poll
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/polls"><i class="fas fa-arrow-left me-1"></i>Back to Polls</a>
                <span class="nav-link text-light">
                    <i class="fas fa-user-shield me-1"></i><?= htmlspecialchars($user['name']) ?>
                </span>
                <form action="/logout" method="POST" class="d-inline">
                    <button type="submit" class="btn btn-outline-light btn-sm ms-2">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div id="alert-container"></div>

    <main class="container py-4">
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
                        <div id="voters-list"></div>
                    </div>
                </div>

                <div id="no-poll-selected-admin" class="text-center py-5">
                    <i class="fas fa-hand-pointer fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">Select a poll to manage voters</h5>
                </div>
            </div>
        </div>
    </main>

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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let adminCurrentPollId = null;
        let votersInterval = null;

        function showAlert(message, type = 'success') {
            const alertHtml = `
                <div class="alert alert-${type} alert-floating alert-dismissible fade show">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            $('#alert-container').html(alertHtml);
            setTimeout(() => { $('.alert-floating').alert('close'); }, 4000);
        }

        function loadAdminPolls() {
            $.get('/api/admin/polls', function(response) {
                if (response.success) renderAdminPollsList(response.polls);
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
                const activeClass = poll.id == adminCurrentPollId ? 'active' : '';
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
                        <small class="text-muted">${poll.active_votes_count || 0} active votes</small>
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
            html += `<thead><tr><th>IP Address</th><th>Current Vote</th><th>Status</th><th>History</th><th>Actions</th></tr></thead><tbody>`;

            voters.forEach(voter => {
                const hasActiveVote = voter.current_vote !== null;
                const statusBadge = hasActiveVote ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Released</span>';
                const currentVote = hasActiveVote ? voter.current_vote.option_text : '-';
                const historyCount = voter.history.length;
                
                html += `
                    <tr>
                        <td><code>${voter.ip_address}</code></td>
                        <td>${currentVote}</td>
                        <td>${statusBadge}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-info" onclick="viewHistory(${pollId}, '${voter.ip_address}')">
                                <i class="fas fa-history me-1"></i>${historyCount} ${historyCount > 1 ? 'entries' : 'entry'}
                            </button>
                        </td>
                        <td>
                            ${hasActiveVote ? `
                                <button class="btn btn-sm btn-danger" onclick="releaseIP(${pollId}, '${voter.ip_address}')">
                                    <i class="fas fa-unlock me-1"></i>Release IP
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
            if (!confirm(`Release IP ${ip}? This will remove their vote and they can vote again.`)) return;

            $.ajax({
                url: '/api/admin/release-ip',
                method: 'POST',
                data: { poll_id: pollId, ip_address: ip },
                success: function(response) {
                    if (response.success) {
                        showAlert('IP released. That vote has been removed.', 'success');
                        fetchVoters(pollId);
                        loadAdminPolls();
                    }
                },
                error: function(xhr) {
                    showAlert(xhr.responseJSON?.message || 'We could not release that IP. Please try again.', 'danger');
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
                if (adminCurrentPollId) fetchVoters(adminCurrentPollId);
            }, 1000); // Real-time updates every second
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
                showAlert('Please add a question and at least two options.', 'warning');
                return;
            }

            $.ajax({
                url: '/api/polls',
                method: 'POST',
                data: { question, options },
                success: function(response) {
                    if (response.success) {
                        showAlert('Your poll has been created!', 'success');
                        $('#createPollModal').modal('hide');
                        $('#createPollForm')[0].reset();
                        loadAdminPolls();
                    }
                },
                error: function(xhr) {
                    showAlert(xhr.responseJSON?.message || 'We could not create the poll. Please try again.', 'danger');
                }
            });
        }

        $(document).ready(function() {
            loadAdminPolls();
            setInterval(loadAdminPolls, 5000);
        });
    </script>
</body>
</html>
