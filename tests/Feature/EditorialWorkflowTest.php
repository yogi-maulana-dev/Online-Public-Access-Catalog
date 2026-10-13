<?php

namespace Tests\Feature;

use App\Enums\BookStatus;
use App\Enums\SubmissionStatus;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Submission;
use App\Models\User;
use App\Services\SubmissionService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditorialWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function makeDraftSubmission(User $author): Submission
    {
        $category = Category::create(['name' => 'Uji', 'slug' => 'uji']);
        $book = Book::create([
            'title' => 'Naskah Uji Kecerdasan Buatan',
            'abstract' => 'Abstrak pengujian alur editorial.',
            'category_id' => $category->id,
            'submitted_by' => $author->id,
            'status' => BookStatus::Draft->value,
            'year' => 2026,
        ]);
        $a = Author::create(['name' => $author->name, 'user_id' => $author->id]);
        $book->authors()->attach($a->id, ['role' => 'author', 'sequence' => 1]);

        return Submission::create([
            'book_id' => $book->id,
            'author_id' => $author->id,
            'title' => $book->title,
            'status' => SubmissionStatus::Pending->value,
        ]);
    }

    public function test_full_workflow_from_submission_to_published(): void
    {
        $author = User::factory()->create();
        $author->assignRole('Author');
        $editor = User::factory()->create();
        $editor->assignRole('Editor');
        $reviewer = User::factory()->create();
        $reviewer->assignRole('Reviewer');

        $service = app(SubmissionService::class);
        $submission = $this->makeDraftSubmission($author);

        // Editor assigns themselves + a reviewer.
        $service->assignEditor($submission, $editor);
        $review = $service->assignReviewer($submission->fresh(), $reviewer);
        $this->assertEquals(SubmissionStatus::UnderReview, $submission->fresh()->status);

        // Reviewer completes the review.
        $service->completeReview($review, [
            'recommendation' => 'minor_revisions',
            'rating' => 4,
            'comments_author' => 'Perbaiki bab 2.',
        ]);
        $this->assertEquals('completed', $review->fresh()->status);

        // Editor requests revisions, then accepts, then publishes.
        $service->requestRevisions($submission->fresh(), 'Mohon revisi minor.');
        $this->assertEquals(2, $submission->fresh()->current_round);

        $service->accept($submission->fresh());
        $service->publish($submission->fresh());

        $submission->refresh();
        $this->assertEquals(SubmissionStatus::Published, $submission->status);
        $this->assertEquals(BookStatus::Published, $submission->book->fresh()->status);
        $this->assertNotNull($submission->book->fresh()->published_at);
    }

    public function test_super_admin_can_reach_filament_dashboard(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/books')->assertOk();
        $this->actingAs($admin)->get('/admin/submissions')->assertOk();
    }

    public function test_reader_cannot_access_admin_panel(): void
    {
        $reader = User::factory()->create();
        $reader->assignRole('Reader');

        $this->actingAs($reader)->get('/admin')->assertForbidden();
    }
}
