<?php
/**
 * Step 7: DSWD Academy Courses
 */

$currentStep = $currentStep ?? 7;
$totalSteps = $totalSteps ?? SURVEY_TOTAL_STEPS;
$errors = $errors ?? [];
$savedData = $savedData ?? [];
?>

<div class="bg-slate-50 pt-8 pb-12">
    <div class="max-w-md md:max-w-2xl lg:max-w-4xl mx-auto px-6 w-full animate-fade-in">
        <div class="mb-10 text-center sm:text-left">
            <h1 class="text-3xl lg:text-4xl font-extrabold tracking-tight text-dswd-dark leading-tight">
                SECTION 7: DSWD ACADEMY COURSES
            </h1>
            <p class="mt-3 text-base lg:text-lg text-slate-500 max-w-2xl">
                Tell us about your DSWD Academy trainings.
            </p>
        </div>

        <form method="POST" action="<?= appUrl('/survey/step/' . $currentStep) ?>" id="surveyForm" class="space-y-8">
            <?= csrfInputField() ?>

            <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                <div class="p-8 md:p-10">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5S19.832 5.477 21 6.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">21. Have you availed of any DSWD Academy trainings?</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php
                        $availedOptions = [
                            'yes' => 'Yes',
                            'no' => 'No',
                        ];
                        foreach ($availedOptions as $value => $label):
                        ?>
                        <label class="group relative flex items-center justify-center p-6 rounded-2xl border border-slate-200 bg-slate-50/50 cursor-pointer transition-all hover:bg-white hover:border-dswd-blue has-[:checked]:bg-white has-[:checked]:border-dswd-blue has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                            <span class="text-lg font-black text-slate-600 group-has-[:checked]:text-dswd-dark transition-colors"><?= htmlspecialchars($label) ?></span>
                            <input type="radio" name="availed_dswd_training" value="<?= htmlspecialchars($value) ?>" <?= ($savedData['availed_dswd_training'] ?? null) === ($value === 'yes') ? 'checked' : '' ?> class="sr-only">
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <?php if (isset($errors['availed_dswd_training'])): ?>
                        <p class="mt-4 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['availed_dswd_training'][0]) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div id="coursesSection" class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden <?= ($savedData['availed_dswd_training'] ?? null) !== true ? 'hidden' : '' ?>">
                <div class="p-8 md:p-10">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 00-2 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                            </div>
                            <h3 class="text-lg font-black text-dswd-dark uppercase tracking-wider">22. If YES, indicate courses taken</h3>
                        </div>
                        <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest px-3 py-1.5 bg-slate-100 rounded-lg self-start">Select Multiple</span>
                    </div>

                    <!-- Tom Select Multi-Dropdown for Courses -->
                    <div class="space-y-4">
                        <div class="flex gap-3">
                            <select
                                id="academy_course_selector"
                                data-tom-select="courses"
                                data-tom-url="<?= htmlspecialchars(appUrl('/api/courses')) ?>"
                                placeholder="Start typing to search courses..."
                                class="flex-1"
                            >
                                <option value="">Select a course to add</option>
                            </select>
                            <button type="button" id="addCourseBtn" class="px-5 py-3 bg-dswd-blue hover:bg-dswd-dark text-white font-black rounded-xl transition-all active:scale-95 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                <span class="hidden sm:inline">Add</span>
                            </button>
                        </div>

                        <!-- Selected Courses Display -->
                        <div id="selectedCoursesContainer" class="flex flex-wrap gap-2 min-h-[48px] p-4 bg-slate-50 rounded-2xl border border-slate-200">
                            <?php
                            $selectedCourses = $savedData['dswd_courses'] ?? [];
                            if (empty($selectedCourses)): ?>
                                <p id="noCoursesPlaceholder" class="text-sm text-slate-400 italic">No courses selected yet. Use the dropdown above to add courses.</p>
                            <?php else: ?>
                                <?php foreach ($selectedCourses as $course): ?>
                                <div class="course-tag inline-flex items-center gap-2 px-4 py-2 bg-white rounded-xl border border-slate-200 shadow-sm">
                                    <span class="text-sm font-bold text-slate-700"><?= htmlspecialchars($course) ?></span>
                                    <button type="button" class="remove-course text-slate-400 hover:text-red-500 transition-colors" data-course="<?= htmlspecialchars($course) ?>">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                    <input type="hidden" name="dswd_courses[]" value="<?= htmlspecialchars($course) ?>">
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Select from DSWD Academy courses. You can add multiple courses.</p>
                    </div>

                    <?php if (isset($errors['dswd_courses'])): ?>
                        <p class="mt-4 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['dswd_courses'][0]) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Enhanced Navigation Bar (Inline) -->
            <div class="mt-12 p-6 md:p-8 bg-white rounded-[2rem] shadow-premium border border-slate-200/60 flex items-center justify-between gap-4">
                <a href="<?= appUrl('/survey/step/' . ($currentStep - 1)) ?>"
                    class="group flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-4 px-8 rounded-2xl transition-all active:scale-[0.98]">
                    <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path>
                    </svg>
                    <span class="hidden sm:inline">Back</span>
                </a>

                <button type="submit"
                    class="flex-1 group relative flex items-center justify-center gap-3 bg-dswd-blue hover:bg-dswd-dark text-white font-black py-4 px-8 rounded-2xl shadow-xl shadow-blue-900/20 transition-all active:scale-[0.98]">
                    <span>Save & Continue</span>
                    <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

