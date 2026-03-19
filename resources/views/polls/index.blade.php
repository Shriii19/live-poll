@extends('layouts.app')

@section('title', 'Polls')

@section('styles')
<style>
    .poll-card-list {
        max-height: 70vh;
        overflow-y: auto;
    }

    .poll-list-item {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .poll-list-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 4px;
        height: 100%;
        background: var(--primary-gradient);
        transform: scaleY(0);
        transition: transform 0.3s ease;
    }

    .poll-list-item:hover::before,
    .poll-list-item.active::before {
        transform: scaleY(1);
    }

    .poll-list-item:hover {
        background: rgba(99, 102, 241, 0.05);
        padding-left: 1.5rem;
    }

    .poll-list-item.active {
        background: rgba(99, 102, 241, 0.1);
        padding-left: 1.5rem;
    }

    .poll-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: var(--primary-gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .vote-count-badge {
        background: rgba(16, 185, 129, 0.15);
        color: #10b981;
        padding: 4px 12px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .empty-state {
        padding: 3rem 2rem;
        text-align: center;
    }

    .empty-state i {
        font-size: 4rem;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        opacity: 0.5;
        margin-bottom: 1rem;
    }

    .result-bar-container {
        position: relative;
        margin-bottom: 1rem;
    }

    .result-option-text {
        position: relative;
        z-index: 2;
        padding: 1rem 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .result-bar-bg {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        border-radius: 12px;
        transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        background: var(--primary-gradient);
        opacity: 0.15;
    }

    .result-percentage {
        font-weight: 700;
        font-size: 1.1rem;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .section-divider {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin: 2rem 0;
    }

    .section-divider::before,
    .section-divider::after {
        content: '';
        flex: 1;
        height: 2px;
        background: linear-gradient(90deg, transparent, rgba(99, 102, 241, 0.3), transparent);
    }

    .section-divider span {
        color: var(--text-secondary);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.1em;
    }
</style>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card" style="animation: fadeIn 0.5s ease;">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="fas fa-list me-2"></i>Active Polls</span>
                <span class="live-indicator">LIVE</span>
            </div>
            <div class="card-body p-0 poll-card-list" id="polls-list">
                <div class="empty-state">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p class="text-muted mb-0">Loading polls...</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div id="poll-detail" class="card" style="display: none; animation: fadeIn 0.5s ease;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="d-flex align-items-center gap-2">
                    <div class="poll-icon">
                        <i class="fas fa-poll"></i>
                    </div>
                    <span id="poll-question" style="font-size: 1.1rem;"></span>
                </span>
                <span class="badge bg-light text-primary" id="total-votes">0 votes</span>
            </div>
            <div class="card-body p-4">
                <div id="voting-section">
                    <h6 class="mb-4 text-muted fw-semibold">
                        <i class="fas fa-hand-pointer me-2"></i>Select your answer
                    </h6>
                    <div id="poll-options"></div>
                    <button id="submit-vote" class="btn btn-primary mt-4 px-4" disabled>
                        <i class="fas fa-vote-yea me-2"></i>Submit Vote
                    </button>
                    <div id="already-voted-msg" class="alert alert-success mt-4" style="display: none; border-radius: 14px;">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Thank you!</strong> You have already voted on this poll.
                    </div>
                </div>

                <div class="section-divider">
                    <span><i class="fas fa-chart-bar me-2"></i>Live Results</span>
                </div>

                <div id="results-section">
                    <div id="poll-results"></div>
                </div>
            </div>
        </div>

        <div id="no-poll-selected" class="text-center py-5" style="animation: fadeIn 0.5s ease;">
            <div class="empty-state">
                <i class="fas fa-hand-pointer" style="opacity: 0.7;"></i>
                <h5 style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 0.5rem;">
                    Select a poll to get started
                </h5>
                <p class="text-muted mb-0">Choose from active polls on the left</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let currentPollId = null;
    let selectedOptionId = null;
    let hasVoted = false;
    let resultsInterval = null;

    // Load polls list
    function loadPolls() {
        $.get('/api/polls', function(response) {
            if (response.success) {
                renderPollsList(response.polls);
            }
        });
    }

    function renderPollsList(polls) {
        const $list = $('#polls-list');
        
        if (polls.length === 0) {
            $list.html(`
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p class="text-muted mb-0">No active polls available</p>
                </div>
            `);
            return;
        }

        let html = '';
        polls.forEach((poll, index) => {
            const activeClass = poll.id === currentPollId ? 'active' : '';
            html += `
                <div class="poll-list-item ${activeClass}" 
                     data-poll-id="${poll.id}" 
                     onclick="loadPoll(${poll.id})"
                     style="animation: fadeIn ${0.3 + index * 0.1}s ease;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="poll-icon">
                            <i class="fas fa-poll-h"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">${poll.question}</div>
                        </div>
                    </div>
                </div>
            `;
        });
        $list.html(html);
    }

    // Load single poll
    function loadPoll(pollId) {
        currentPollId = pollId;
        selectedOptionId = null;
        hasVoted = false;

        // Update active state in list
        $('.poll-list-item').removeClass('active');
        $(`.poll-list-item[data-poll-id="${pollId}"]`).addClass('active');

        $.get(`/api/polls/${pollId}`, function(response) {
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

        // Render options
        let optionsHtml = '';
        poll.options.forEach((option, index) => {
            let classes = 'poll-option list-group-item';
            let icon = 'fa-circle';
            if (userHasVoted && option.id == votedOptionId) {
                classes += ' voted';
                icon = 'fa-check-circle';
            }
            
            optionsHtml += `
                <div class="${classes}" data-option-id="${option.id}" 
                     onclick="selectOption(${option.id}, ${userHasVoted})"
                     style="animation: fadeIn ${0.3 + index * 0.1}s ease;">
                    <div class="d-flex align-items-center justify-content-between">
                        <span><i class="fas ${icon} me-3"></i>${option.option_text}</span>
                        ${userHasVoted && option.id == votedOptionId ? '<span class="badge bg-success">Your vote</span>' : ''}
                    </div>
                </div>
            `;
        });
        $('#poll-options').html(`<div class="list-group">${optionsHtml}</div>`);

        // Handle voting UI
        if (userHasVoted) {
            $('#submit-vote').hide();
            $('#already-voted-msg').show();
        } else {
            $('#submit-vote').show().prop('disabled', true);
            $('#already-voted-msg').hide();
        }

        // Load results
        loadResults(poll.id);
    }

    function selectOption(optionId, userHasVoted) {
        if (userHasVoted) return;

        selectedOptionId = optionId;
        
        // Update UI
        $('.poll-option').removeClass('selected');
        $('.poll-option i').removeClass('fa-check-circle').addClass('fa-circle');
        $(`.poll-option[data-option-id="${optionId}"]`).addClass('selected');
        $(`.poll-option[data-option-id="${optionId}"] i`).removeClass('fa-circle').addClass('fa-check-circle');
        $('#submit-vote').prop('disabled', false);
    }

    // Submit vote
    $('#submit-vote').on('click', function() {
        if (!selectedOptionId || hasVoted) return;

        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Submitting...');

        $.ajax({
            url: '/api/vote',
            method: 'POST',
            data: {
                poll_id: currentPollId,
                option_id: selectedOptionId
            },
            success: function(response) {
                if (response.success) {
                    showAlert('Your vote has been submitted successfully!', 'success');
                    loadPoll(currentPollId); // Reload to update UI
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Failed to submit vote';
                showAlert(message, 'danger');
                $btn.prop('disabled', false).html('<i class="fas fa-vote-yea me-2"></i>Submit Vote');
            }
        });
    });

    // Load and render results
    function loadResults(pollId) {
        $.get(`/api/polls/${pollId}/results`, function(response) {
            if (response.success) {
                renderResults(response.results, response.total_votes);
            }
        });
    }

    function renderResults(results, totalVotes) {
        $('#total-votes').html(`<i class="fas fa-users me-1"></i>${totalVotes} vote${totalVotes !== 1 ? 's' : ''}`);

        let html = '';
        results.forEach((result, index) => {
            const percentage = totalVotes > 0 ? Math.round((result.vote_count / totalVotes) * 100) : 0;
            html += `
                <div class="result-bar-container" style="animation: fadeIn ${0.3 + index * 0.1}s ease; border-radius: 12px; background: rgba(241, 245, 249, 0.8); overflow: hidden;">
                    <div class="result-bar-bg" style="width: ${percentage}%"></div>
                    <div class="result-option-text">
                        <span class="fw-medium">${result.option_text}</span>
                        <span>
                            <span class="text-muted me-2">${result.vote_count} votes</span>
                            <span class="result-percentage">${percentage}%</span>
                        </span>
                    </div>
                </div>
            `;
        });
        $('#poll-results').html(html);
    }

    // Real-time results polling (every 1 second)
    function startResultsPolling() {
        if (resultsInterval) {
            clearInterval(resultsInterval);
        }
        
        resultsInterval = setInterval(function() {
            if (currentPollId) {
                loadResults(currentPollId);
            }
        }, 1000);
    }

    function stopResultsPolling() {
        if (resultsInterval) {
            clearInterval(resultsInterval);
            resultsInterval = null;
        }
    }

    // Initialize
    $(document).ready(function() {
        loadPolls();

        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopResultsPolling();
                return;
            }

            if (currentPollId) {
                startResultsPolling();
                loadResults(currentPollId);
            }
        });

        window.addEventListener('beforeunload', function() {
            stopResultsPolling();
        });
        
        // Refresh polls list every 10 seconds
        setInterval(loadPolls, 10000);
    });
</script>
@endsection
