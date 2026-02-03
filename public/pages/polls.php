<?php
require_once __DIR__ . '/../../config.php';

if (!isLoggedIn()) {
    redirect('/login');
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Polls - Live Poll Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand { font-weight: bold; }
        .card { border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); transition: transform 0.2s; }
        .card:hover { transform: translateY(-2px); }
        .poll-option { cursor: pointer; transition: all 0.2s; }
        .poll-option:hover { background-color: #e9ecef; }
        .poll-option.selected { background-color: #0d6efd; color: white; }
        .poll-option.voted { background-color: #198754; color: white; }
        .progress { height: 25px; }
        .progress-bar { transition: width 0.3s ease; }
        .alert-floating { position: fixed; top: 80px; right: 20px; z-index: 1050; min-width: 300px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/polls">
                <i class="fas fa-poll me-2"></i>Live Poll
            </a>
            <div class="navbar-nav ms-auto">
                <?php if ($user['is_admin']): ?>
                <a class="nav-link" href="/admin">
                    <i class="fas fa-cog me-1"></i>Admin
                </a>
                <?php endif; ?>
                <span class="nav-link text-light">
                    <i class="fas fa-user me-1"></i><?= htmlspecialchars($user['name']) ?>
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
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-list me-2"></i>Active Polls
                    </div>
                    <div class="card-body p-0">
                        <div id="polls-list" class="list-group list-group-flush">
                            <div class="text-center py-4">
                                <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div id="poll-detail" class="card" style="display: none;">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-poll me-2"></i><span id="poll-question"></span></span>
                        <span class="badge bg-light text-primary" id="total-votes">0 votes</span>
                    </div>
                    <div class="card-body">
                        <div id="voting-section">
                            <h6 class="mb-3">Select your answer:</h6>
                            <div id="poll-options"></div>
                            <button id="submit-vote" class="btn btn-primary mt-3" disabled>
                                <i class="fas fa-vote-yea me-2"></i>Submit Vote
                            </button>
                            <div id="already-voted-msg" class="alert alert-info mt-3" style="display: none;">
                                <i class="fas fa-check-circle me-2"></i>You have already voted on this poll.
                            </div>
                        </div>

                        <hr class="my-4">

                        <div id="results-section">
                            <h6 class="mb-3"><i class="fas fa-chart-bar me-2"></i>Live Results <small class="text-muted">(updates every second)</small></h6>
                            <div id="poll-results"></div>
                        </div>
                    </div>
                </div>

                <div id="no-poll-selected" class="text-center py-5">
                    <i class="fas fa-hand-pointer fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">Select a poll from the list to view and vote</h5>
                </div>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentPollId = null;
        let selectedOptionId = null;
        let hasVoted = false;
        let resultsInterval = null;

        function showAlert(message, type = 'success') {
            const alertHtml = `
                <div class="alert alert-${type} alert-floating alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            $('#alert-container').html(alertHtml);
            setTimeout(() => { $('.alert-floating').alert('close'); }, 4000);
        }

        function loadPolls() {
            $.get('/api/polls.php', function(response) {
                if (response.success) {
                    renderPollsList(response.polls);
                }
            });
        }

        function renderPollsList(polls) {
            const $list = $('#polls-list');
            if (polls.length === 0) {
                $list.html('<div class="text-center py-4 text-muted">No active polls available</div>');
                return;
            }
            let html = '';
            polls.forEach(poll => {
                const activeClass = poll.id == currentPollId ? 'active' : '';
                html += `
                    <a href="#" class="list-group-item list-group-item-action ${activeClass}" 
                       data-poll-id="${poll.id}" onclick="loadPoll(${poll.id}); return false;">
                        <i class="fas fa-poll-h me-2"></i>${poll.question}
                    </a>
                `;
            });
            $list.html(html);
        }

        function loadPoll(pollId) {
            currentPollId = pollId;
            selectedOptionId = null;
            hasVoted = false;

            $('#polls-list .list-group-item').removeClass('active');
            $(`#polls-list [data-poll-id="${pollId}"]`).addClass('active');

            $.get(`/api/polls.php?id=${pollId}&action=get`, function(response) {
                if (response.success) {
                    renderPollDetail(response.poll, response.has_voted, response.voted_option);
                    startResultsPolling();
                }
            });
        }

        function renderPollDetail(poll, userHasVoted, votedOptionId) {
            hasVoted = userHasVoted;
            $('#poll-question').text(poll.question);
            $('#no-poll-selected').hide();
            $('#poll-detail').show();

            let optionsHtml = '';
            poll.options.forEach(option => {
                let classes = 'poll-option list-group-item list-group-item-action';
                if (userHasVoted && option.id == votedOptionId) {
                    classes += ' voted';
                }
                optionsHtml += `
                    <div class="${classes}" data-option-id="${option.id}" 
                         onclick="selectOption(${option.id}, ${userHasVoted})">
                        <i class="fas fa-circle me-2"></i>${option.option_text}
                        ${userHasVoted && option.id == votedOptionId ? '<i class="fas fa-check float-end"></i>' : ''}
                    </div>
                `;
            });
            $('#poll-options').html(`<div class="list-group">${optionsHtml}</div>`);

            if (userHasVoted) {
                $('#submit-vote').hide();
                $('#already-voted-msg').show();
            } else {
                $('#submit-vote').show().prop('disabled', true);
                $('#already-voted-msg').hide();
            }

            loadResults(poll.id);
        }

        function selectOption(optionId, userHasVoted) {
            if (userHasVoted) return;
            selectedOptionId = optionId;
            $('.poll-option').removeClass('selected');
            $(`.poll-option[data-option-id="${optionId}"]`).addClass('selected');
            $('#submit-vote').prop('disabled', false);
        }

        $('#submit-vote').on('click', function() {
            if (!selectedOptionId || hasVoted) return;

            const $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Voting...');

            $.ajax({
                url: '/api/vote.php',
                method: 'POST',
                data: { poll_id: currentPollId, option_id: selectedOptionId },
                success: function(response) {
                    if (response.success) {
                        showAlert('Thanks! Your vote has been submitted.', 'success');
                        loadPoll(currentPollId);
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || "We couldn't submit your vote. Please try again.";
                    showAlert(message, 'danger');
                    $btn.prop('disabled', false).html('<i class="fas fa-vote-yea me-2"></i>Submit Vote');
                }
            });
        });

        function loadResults(pollId) {
            $.get(`/api/polls.php?id=${pollId}&action=results`, function(response) {
                if (response.success) {
                    renderResults(response.results, response.total_votes);
                }
            });
        }

        function renderResults(results, totalVotes) {
            $('#total-votes').text(`${totalVotes} vote${totalVotes !== 1 ? 's' : ''}`);
            let html = '';
            results.forEach(result => {
                const percentage = totalVotes > 0 ? Math.round((result.vote_count / totalVotes) * 100) : 0;
                html += `
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>${result.option_text}</span>
                            <span>${result.vote_count} (${percentage}%)</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: ${percentage}%"></div>
                        </div>
                    </div>
                `;
            });
            $('#poll-results').html(html);
        }

        function startResultsPolling() {
            if (resultsInterval) clearInterval(resultsInterval);
            resultsInterval = setInterval(function() {
                if (currentPollId) loadResults(currentPollId);
            }, 1000); // Updates every 1 second (real-time)
        }

        $(document).ready(function() {
            loadPolls();
            setInterval(loadPolls, 10000);
        });
    </script>
</body>
</html>
