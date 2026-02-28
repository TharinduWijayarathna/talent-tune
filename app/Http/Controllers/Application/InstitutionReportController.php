<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Viva;
use App\Models\VivaStudentSubmission;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InstitutionReportController extends Controller
{
    /**
     * Reports index page for institution admin.
     */
    public function index(Request $request)
    {
        $institution = $request->user()->institution;
        if (! $institution) {
            abort(403);
        }

        return Inertia::render('institution/Reports', []);
    }

    /**
     * Download students report (batch-wise) as PDF.
     */
    public function studentsPdf(Request $request)
    {
        $institution = $request->user()->institution;
        if (! $institution) {
            abort(403);
        }

        $students = User::forInstitution($institution->id)
            ->where('role', 'student')
            ->orderBy('batch')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'student_id', 'batch', 'department', 'created_at']);

        $completedCounts = VivaStudentSubmission::whereIn('student_id', $students->pluck('id'))
            ->where('status', 'completed')
            ->selectRaw('student_id, count(*) as count')
            ->groupBy('student_id')
            ->pluck('count', 'student_id');

        $students = $students->map(fn ($s) => [
            'name' => $s->name,
            'email' => $s->email,
            'student_id' => $s->student_id,
            'batch' => $s->batch ?? '—',
            'department' => $s->department ?? '—',
            'completed_vivas' => $completedCounts->get($s->id, 0),
            'created_at' => $s->created_at->format('Y-m-d'),
        ]);

        $byBatch = $students->groupBy('batch')->map(fn ($items) => $items->values()->all())->sortKeys();

        $pdf = Pdf::loadView('reports.institution.students', [
            'institutionName' => $institution->name,
            'byBatch' => $byBatch,
            'generatedAt' => now()->format('Y-m-d H:i:s'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('students-report-'.$institution->slug.'-'.date('Y-m-d').'.pdf');
    }

    /**
     * Download lecturers report (vivas and submissions) as PDF.
     */
    public function lecturersPdf(Request $request)
    {
        $institution = $request->user()->institution;
        if (! $institution) {
            abort(403);
        }

        $lecturers = User::forInstitution($institution->id)
            ->where('role', 'lecturer')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'employee_id', 'department']);

        $vivaCounts = Viva::where('institution_id', $institution->id)
            ->selectRaw('lecturer_id, count(*) as total_vivas')
            ->groupBy('lecturer_id')
            ->pluck('total_vivas', 'lecturer_id');

        $completedVivaIds = Viva::where('institution_id', $institution->id)->where('status', 'completed')->pluck('id');
        $completedSubmissionsByViva = VivaStudentSubmission::whereIn('viva_id', $completedVivaIds)
            ->where('status', 'completed')
            ->selectRaw('viva_id, count(*) as c')
            ->groupBy('viva_id')
            ->pluck('c', 'viva_id');

        $vivasByLecturer = Viva::where('institution_id', $institution->id)
            ->get(['id', 'lecturer_id'])
            ->groupBy('lecturer_id');

        $rows = $lecturers->map(function ($lec) use ($vivaCounts, $vivasByLecturer, $completedSubmissionsByViva) {
            $totalVivas = $vivaCounts->get($lec->id, 0);
            $vivas = $vivasByLecturer->get($lec->id, collect());
            $totalSubmissions = $vivas->sum(fn ($v) => $completedSubmissionsByViva->get($v->id, 0));

            return [
                'name' => $lec->name,
                'email' => $lec->email,
                'employee_id' => $lec->employee_id ?? '—',
                'department' => $lec->department ?? '—',
                'total_vivas' => $totalVivas,
                'completed_submissions' => $totalSubmissions,
            ];
        });

        $pdf = Pdf::loadView('reports.institution.lecturers', [
            'institutionName' => $institution->name,
            'lecturers' => $rows,
            'generatedAt' => now()->format('Y-m-d H:i:s'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('lecturers-report-'.$institution->slug.'-'.date('Y-m-d').'.pdf');
    }
}
