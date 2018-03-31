<?php
require_once('autoload.php');
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
$c = $course;
$course = new uet_course($course->id);
$user = new uet_user($USER->id);
$view->setPage($url);
$uet = new uet_analytics($c);
$result = $uet->predict($user->getUserId());

echo $OUTPUT->header();
?>

    <div class="main-panel">
        <?php echo $view->navbar() ?>

        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <?php echo $view->userInformation() ?>
                    <?php echo $view->courseInformation() ?>
                </div>
                <?php if (!$user->isTeacher($view->getContext())) { ?>
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
                                                            <h4>Giữa kỳ</h4>
                                                            <!--                                                        <div class="icon-big icon-warning text-center">-->
                                                            <!--                                                            <i class="ti-server"></i>-->
                                                            <!--                                                        </div>-->
                                                        </div>
                                                        <div class="col-xs-4">
                                                            <div class="numbers">
                                                                <p>Dự báo</p>
                                                                <?php  if(isset($result->w7)) echo $result->w7  ?>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-4">
                                                            <div class="numbers">
                                                                <p>Thực tế</p>
                                                                7.5
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
                                                            <!--                                                        <div class="icon-big icon-warning text-center">-->
                                                            <!--                                                            <i class="ti-server"></i>-->
                                                            <!--                                                        </div>-->
                                                        </div>
                                                        <div class="col-xs-4">
                                                            <div class="numbers">
                                                                <p>Dự báo</p>
                                                                <?php if(isset($result->w15)) echo $result->w15?>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-4">
                                                            <div class="numbers">
                                                                <p>Thực tế</p>
                                                                7.5
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
                                        <div class="col-lg-6 col-sm-6">
                                            <div class="card">
                                                <div class="content">
                                                    <div class="row">
                                                        <div class="col-xs-5">
                                                            <div class="icon-big icon-danger text-center">
                                                                <i class="ti-bell"></i>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-7">
                                                            <div class="numbers">
                                                                <p>Xếp loại</p>
                                                                Tốt
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
                                                        <div class="col-xs-5">
                                                            <div class="icon-big icon-danger text-center">
                                                                <i class="ti-pulse"></i>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-7">
                                                            <div class="numbers">
                                                                <p>Gợi ý</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="footer">
                                                        <hr/>
                                                        <div class="stats">
                                                            <i class="ti-timer"></i> In the last hour
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="footer">
                                        <div class="chart-legend">
                                            <!--                                        <i class="fa fa-circle "  style="color: #68B3C8"></i> View-->
                                            <!--                                        <i class="fa fa-circle " style="color: #F3BB45"></i> Post-->
                                            <!--                                        <i class="fa fa-circle " style="color: #EB5E28" ></i> Forum view-->
                                            <!--                                        <i class="fa fa-circle " style="color: #7AC29A"></i> Forum post-->
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
                                    <div class="stats">
                                        <i class="ti-reload"></i>
                                    </div>
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

                                <div id="chartAssignment" class="ct-chart ct-perfect-fourth" ></div>

                                <?php
                                if ($user->isTeacher($view->getContext())) {
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
                                        <?php if (!$user->isTeacher($view->getContext())) { ?>
                                            <i class="fa fa-circle" style="color: #68B3C8"></i> Submitted
                                            <i class="fa fa-circle text-warning"></i> Not Submitted
                                        <?php } ?>
                                    </div>
                                    <hr>
                                    <div class="stats">
                                        <i class="ti-timer"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if ($user->isTeacher($view->getContext())) { ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="header">
                                    <h4 class="title">Thông tin sinh viên</h4>
                                    <p class="category">Thống kê hoạt động của từng sinh viên</p>
                                </div>
                                <div class="content table-responsive table-full-width">
                                    <table class="table table-str" style="">
                                        <thead>
                                        <tr>
                                            <th>STT</th>
                                            <th>Userid</th>
                                            <th>Name</th>
                                            <th>View</th>
                                            <th>Post</th>
                                            <th>Forum</th>
                                            <th>Assignment</th>
                                            <th>Grade</th>
                                        </tr>
                                        </thead>
                                        <tbody>

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

<?php
echo $OUTPUT->footer();