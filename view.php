<?php
require_once('autoload.php');

use mod_uetanalytics\uet_course;
use mod_uetanalytics\uet_analytics;
use mod_uetanalytics\uet_user;
use mod_uetanalytics\uet_student;
use mod_uetanalytics\uet_render;

global $USER, $DB, $PAGE;
$id = optional_param('id', 0, PARAM_INT);    // Course Module ID, or
$l = optional_param('l', 0, PARAM_INT);     // Label ID

if ($id) {
    $url = new moodle_url('/mod/uetanalytics/index.php', array('id' => $id));
    if (!$cm = get_coursemodule_from_id('uetanalytics', $id)) {
        print_error('invalidcoursemodule');
    }

    if (!$course = $DB->get_record("course", array("id" => $cm->course))) {
        print_error('coursemisconf');
    }

    if (!$label = $DB->get_record("uetanalytics", array("id" => $cm->instance))) {
        print_error('invalidcoursemodule');
    }

} else {
    $url = new moodle_url('/mod/uetanalytics/index.php', array('l' => $l));
    if (!$label = $DB->get_record("uetanalytics", array("id" => $l))) {
        print_error('invalidcoursemodule');
    }
    if (!$course = $DB->get_record("course", array("id" => $label->course))) {
        print_error('coursemisconf');
    }
    if (!$cm = get_coursemodule_from_instance("uetanalytics", $label->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
}

require_login();

$view = new uet_render($cm, $USER->id);
$course = new uet_course($course->id);
$user = new uet_user($USER->id);
$isTeacher = $user->isTeacher($view->getContext());
if (!$isTeacher) {
    $user = new uet_student($user->getUserId(), $course->getCourseId());
}
$view->setPage($url);
$uet = new uet_analytics($course);

echo $OUTPUT->header();
?>
    <script type="text/javascript">
        var student = <?php echo $view->studentArray() ?>;
        var coursid = <?php echo $course->getCourseId() ?>;
    </script>
    <div class="main-panel" id="main">
        <?php echo $view->navbar() ?>

        <div class="content" style="overflow-y:hidden">
            <div class="container-fluid">
                <div class="row">
                    <?php echo $view->userInformation() ?>
                    <?php echo $view->courseInformation() ?>
                </div>
                <?php

                if (!$isTeacher) {
                    $result = $uet->predict($user->getUserId());
                    $grade = $uet->getGrade($user->getUserId());
                    ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="header">
                                    <h4 class="title">Dự báo kết quả học tập</h4>
                                    <p class="category"></p>
                                </div>
                                <div class="content">
                                    <div class="row">
                                        <div class="col-lg-6 col-sm-6">
                                            <div class="card">
                                                <div class="content">
                                                    <div class="row">
                                                        <div class="col-xs-4">
                                                            <h4>Giữa kỳ</h4></div>
                                                        <div class="col-xs-4">
                                                            <div class="numbers">
                                                                <p>Dự báo</p>
                                                                <?php if (isset($result->w7)) echo $result->w7 ?>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-4">
                                                            <div class="numbers">
                                                                <p>Thực tế</p>
                                                                <?php echo $grade->mid ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="footer">
                                                        <hr/>
                                                        <div class="stats">
                                                            <i class="ti-reload"></i> Updated now
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-sm-6">
                                            <div class="card">
                                                <div class="content">
                                                    <div class="row">
                                                        <div class="col-xs-4">
                                                            <h4>Cuối kỳ</h4>
                                                        </div>

                                                        <div class="col-xs-4">
                                                            <div class="numbers">
                                                                <p>Dự báo</p>
                                                                <?php if (isset($result->w15)) echo $result->w15 ?>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-4">
                                                            <div class="numbers">
                                                                <p>Thực tế</p>
                                                                <?php echo $grade->final ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="footer">
                                                        <hr/>
                                                        <div class="stats">
                                                            <i class="ti-reload"></i> Updated now
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="card">
                                                <div class="content">
                                                    <div class="row">
                                                        <div class="col-xs-4">
                                                            <h4>Thông báo:</h4></div>
                                                        <div class="col-xs-8">
                                                            <?php if ($user instanceof uet_student) echo $user->getNotification() ?>
                                                        </div>

                                                    </div>
                                                    <div class="footer">
                                                        <hr/>
                                                        <div class="stats">
                                                            <i class="ti-reload"></i> Updated now
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="footer">
                                        <div class="chart-legend">
                                        </div>
                                        <hr>
                                        <div class="stats">
                                            <i class="ti-reload"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                <?php } ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="header">
                                <h4 class="title"> Thống kê truy cập</h4>
                                <p class="category"></p>
                            </div>
                            <div class="content">
                                <div id="chartViewPost" class="ct-chart"></div>
                                <div class="footer">
                                    <div class="chart-legend">
                                        <i class="fa fa-circle " style="color: #68B3C8"></i> View
                                        <i class="fa fa-circle " style="color: #F3BB45"></i> Post
                                        <i class="fa fa-circle " style="color: #EB5E28"></i> Forum view
                                        <i class="fa fa-circle " style="color: #7AC29A"></i> Forum post
                                    </div>
                                    <hr>

                                </div>
                            </div>
                        </div>
                    </div>
                    <?php $view->lineChartScript('chartViewPost') ?>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="header">
                                <h4 class="title">Thống kê nộp bài tập</h4>
                                <p class="category"></p>
                            </div>
                            <div class="content">

                                <div id="chartAssignment" class="ct-chart "></div>

                                <?php
                                if ($isTeacher) {
                                    $view->submitLineChart('chartAssignment');
                                } else {
                                    ?>
                                    <script type="text/javascript">

                                        <?php
                                        echo $view->pieChartScript('chartAssignment')
                                        ?>
                                    </script>
                                <?php } ?>
                                <div class="footer">
                                    <div class="chart-legend">
                                        <?php if (!$isTeacher) { ?>
                                            <i class="fa fa-circle" style="color: #68B3C8"></i> Submitted
                                            <i class="fa fa-circle text-warning"></i> Not Submitted
                                        <?php } ?>
                                    </div>
                                    <hr>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if ($isTeacher) { ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="header">
                                    <h4 class="title">Thông tin sinh viên</h4>
                                    <p class="category">Thống kê hoạt động của từng sinh viên</p>
                                    <a href="gradeimport.php?mod=<?php echo $id ?>&course=<?php echo $course->getCourseId() ?>">Import
                                        Grade</a>
                                </div>

                                <div class="content table-responsive table-full-width">
                                    <table class="table table-str" style="">
                                        <thead>
                                        <tr>
                                            <th>STT</th>
                                            <th>Name</th>
                                            <th>View</th>
                                            <th>Post</th>
                                            <th>Forum</th>
                                            <th>Assignment</th>
                                            <th>Predict grade</th>
                                            <th>Real grade</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody id="table-statis">

                                        <?php
                                        $students = $course->getStudents();
                                        $index = 1;
                                        foreach ($students as $student) {
                                            $student = new uet_student($student->userid, $course->getCourseId());
                                            $student->setupStudent();
                                            ?>
                                            <tr>
                                                <td> <?php echo $index;
                                                    $index++; ?></td>
                                                <td> <?php echo $student->getName(); ?></td>
                                                <td> <?php echo $student->getView() ?></td>
                                                <td> <?php echo $student->getPost() ?></td>
                                                <td> <?php echo 'view: ' . $student->getForumView() . ' Post: ' . $student->getForumPost() ?></td>
                                                <td>
                                                    <?php
                                                    $submission = $student->getSubmission();
                                                    echo 'Submitted: ' . $submission->submitted . '/' . $submission->total . '<br>';
                                                    echo 'Late: ' . $submission->late . '/' . $submission->submitted . '<br>';
                                                    echo 'Not submit: ' . $submission->not . '/' . $submission->total;
                                                    ?>
                                                </td>
                                                <td><?php $predict = $student->getPredict();
                                                    echo 'GK: ' . $predict->w7;
                                                    echo ' CK: ' . $predict->w15 ?></td>
                                                <td><?php $grade = $student->getGrade();
                                                    echo 'GK: ' . $grade->mid;
                                                    echo ' CK: ' . $grade->final ?></td>
                                                <td>
                                                    <a href="#" class="message-btn"
                                                       data-from="<?php echo $user->getUserId() ?>"
                                                       data-to="<?php echo $student->getUserId(); ?>"><span
                                                                class="ti-email"></span></a>
                                                    <a href="#" style="margin-left: 7px;" class="notify-btn"
                                                       data-from="<?php echo $user->getUserId() ?>"
                                                       data-to="<?php echo $student->getUserId(); ?>"><span
                                                                class="ti-alert"></span></a>
                                                </td>
                                            </tr>
                                            <?php
                                        } ?>
                                        </tbody>

                                    </table>

                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>

    </div>
    <div id="message-popup" class="col-lg-6 message-popup">
        <div class="card">
            <div class="header">
                <h4 class="title" id="message-title">Send Message</h4>
            </div>
            <div class="content">
                <form>
                    <input type="hidden" name="courseid" value="<?php echo $course->getCourseId() ?>">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>From: </label>
                                <input type="text" id="message-from" class="form-control border-input" value=""
                                       disabled>
                                <input type="hidden" name="from" value="">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>To:</label>
                                <input type="text" id="message-to" class="form-control border-input" value="" disabled>
                                <input style="display: none" type="hidden" name="to" value="">
                            </div>
                        </div>
                    </div>
                    <div id="subject" class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Subject: </label>
                                <input type="text" name="msgsubject" class="form-control border-input" value="">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Message: </label>
                                <textarea rows="5" class="form-control border-input"
                                          name="msgmessage" placeholder="Enter text here!"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="button" id="send" data-type="message" class="btn btn-info btn-fill btn-wd">Send
                        </button>
                        <button type="button" id="cancel" class="btn btn-info btn-fill btn-wd">Cancel</button>
                    </div>
                    <div class="clearfix"></div>
                </form>
            </div>
        </div>
    </div>

<?php
echo $OUTPUT->footer();
