<?php

namespace App\Services;

use App\Enums\SubmissionStatus;
use App\Models\ActivityLog;
use App\Models\Book;
use App\Models\Review;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Drives the editorial workflow:
 * pending -> under_review -> revisions_requested -> accepted -> published (or rejected).
 */
class SubmissionService
{
    public function __construct(private BookService $books)
    {
    }

    /**
     * Assign an editor and move the submission into review.
     */
    public function assignEditor(Submission $submission, User $editor): Submission
    {
        $submission->update([
            'editor_id' => $editor->id,
            'status' => SubmissionStatus::UnderReview,
            'stage' => 'review',
        ]);

        ActivityLog::record('submission.editor_assigned', $submission, "Editor {$editor->name} ditugaskan");

        return $submission;
    }

    /**
     * Invite a reviewer for the current round.
     */
    public function assignReviewer(Submission $submission, User $reviewer, ?string $dueDate = null): Review
    {
        $review = Review::firstOrCreate(
            [
                'submission_id' => $submission->id,
                'reviewer_id' => $reviewer->id,
                'round' => $submission->current_round,
            ],
            [
                'status' => 'assigned',
                'due_date' => $dueDate,
            ],
        );

        if ($submission->status === SubmissionStatus::Pending) {
            $submission->update(['status' => SubmissionStatus::UnderReview, 'stage' => 'review']);
        }

        ActivityLog::record('submission.reviewer_assigned', $submission, "Reviewer {$reviewer->name} ditugaskan");

        return $review;
    }

    /**
     * A reviewer submits their recommendation and comments.
     */
    public function completeReview(Review $review, array $data): Review
    {
        $review->update([
            'recommendation' => $data['recommendation'],
            'rating' => $data['rating'] ?? null,
            'comments_author' => $data['comments_author'] ?? null,
            'comments_editor' => $data['comments_editor'] ?? null,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        ActivityLog::record('review.completed', $review->submission, 'Review diselesaikan');

        return $review;
    }

    /**
     * Editor requests revisions; opens a new round.
     */
    public function requestRevisions(Submission $submission, ?string $note = null): Submission
    {
        $submission->update([
            'status' => SubmissionStatus::RevisionsRequested,
            'current_round' => $submission->current_round + 1,
            'decision_note' => $note,
        ]);

        ActivityLog::record('submission.revisions_requested', $submission, $note);

        return $submission;
    }

    public function accept(Submission $submission, ?string $note = null): Submission
    {
        $submission->update([
            'status' => SubmissionStatus::Accepted,
            'stage' => 'production',
            'decision_note' => $note,
            'decided_at' => now(),
        ]);

        ActivityLog::record('submission.accepted', $submission, $note);

        return $submission;
    }

    public function reject(Submission $submission, ?string $note = null): Submission
    {
        $submission->update([
            'status' => SubmissionStatus::Rejected,
            'decision_note' => $note,
            'decided_at' => now(),
        ]);

        ActivityLog::record('submission.rejected', $submission, $note);

        return $submission;
    }

    /**
     * Final step: publish the linked book and mark submission published.
     */
    public function publish(Submission $submission): Submission
    {
        return DB::transaction(function () use ($submission) {
            $this->books->publish($submission->book);

            $submission->update([
                'status' => SubmissionStatus::Published,
                'stage' => 'production',
                'decided_at' => now(),
            ]);

            ActivityLog::record('submission.published', $submission, 'Buku diterbitkan');

            return $submission;
        });
    }
}
