@extends('layouts.app')

@section('title', 'Polls')

@section('content')
<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card" style="animation: fadeIn 0.5s ease;">
            <div class="card-header">
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
        <div id="poll-detail" class="card" style="display: none; animation: fadeIn 0.5s ease;">
            <div class="card-header d-flex justify-content-between align-items-center">
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
                    <h6 class="mb-3"><i class="fas fa-chart-bar me-2"></i>Live Results</h6>
                    <div id="poll-results"></div>
                </div>
            </div>
        </div>

        <div id="no-poll-selected" class="text-center py-5" style="animation: fadeIn 0.5s ease;">
            <i class="fas fa-hand-pointer fa-4x mb-3" style="color: #667eea; opacity: 0.7;"></i>
            <h5 style="color: #667eea;">Select a poll from the list to view and vote</h5>
            <p class="text-muted">Choose from the active polls on the left</p>
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
            $list.html('<div class="text-center py-4 text-muted">No active polls available</div>');
            return;
        }

        let html = '';
        polls.forEach(poll => {
            const activeClass = poll.id === currentPollId ? 'active' : '';
            html += `
                <a href="#" class="list-group-item list-group-item-action ${activeClass}" 
                   data-poll-id="${poll.id}" onclick="loadPoll(${poll.id}); return false;">
                    <i class="fas fa-poll-h me-2"></i>${poll.question}
                </a>
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
        $('#polls-list .list-group-item').removeClass('active');
        $(`#polls-list [data-poll-id="${pollId}"]`).addClass('active');

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
        $(`.poll-option[data-option-id="${optionId}"]`).addClass('selected');
        $('#submit-vote').prop('disabled', false);
    }

    // Submit vote
    $('#submit-vote').on('click', function() {
        if (!selectedOptionId || hasVoted) return;

        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Voting...');

        $.ajax({
            url: '/api/vote',
            method: 'POST',
            data: {
                poll_id: currentPollId,
                option_id: selectedOptionId
            },
            success: function(response) {
                if (response.success) {
                    showAlert('Vote submitted successfully!', 'success');
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

    // Initialize
    $(document).ready(function() {
        loadPolls();
        
        // Refresh polls list every 10 seconds
        setInterval(loadPolls, 10000);
    });
</script>
@endsection
