<?php
/**
 * Created by PhpStorm.
 * User: bachnguyen
 * Date: 3/9/18
 * Time: 8:36 AM
 */


class uet_render
{
    private $uet;
    private $user;
    private $course;
    private $context;
    private $cm;

    public function __construct($cm, $userid)
    {
        $this->cm = $cm;
        $this->course = new uet_course($cm->course);
        $this->user = new uet_user($userid);
        $this->uet = new uet_analytics($this->course->getCourse());
        $this->context = context_module::instance($cm->id);
    }

    public function getContext()
    {
        return $this->context;
    }

    public function navbar()
    {
        $navbar = ' <nav class="navbar navbar-default">
                <div class="container-fluid">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar bar1"></span>
                            <span class="icon-bar bar2"></span>
                            <span class="icon-bar bar3"></span>
                        </button>
                        <a class="navbar-brand" href="#">Dashboard</a>
                    </div>
            </nav>';

        return $navbar;
    }

    public function userInformation()
    {
        global $CFG, $OUTPUT;
        $options = [
            'class' => 'avatar border-white',
            'size' => '100px',
            'link' => false
        ];
        $avatar = $this->user->getProfilePicture();
        $render = '<div class="col-lg-6 col-md-6">
            <div class="card card-user">
                <div class="image">
                    <img src="assets/img/background.jpg" alt="..."/>
                </div>
                <div class="content">
                    <div class="author">
                        <img class="avatar border-white" src="' . $avatar . '" alt="..."/>
                        <h4 class="title">' . $this->user->getName() . '<br/>
                            <a href="#">
                                <small>' . $this->user->getEmail() . '</small>
                            </a>
                        </h4>
                    </div>
                    <p class="description text-center">
                         ' . $this->user->getAddress() . '
                    </p>
                </div>
                <hr>
               <div class="text-center">
                    <div class="row">
                        <div class="col-md-3 col-md-offset-1">
                             <h5>' . $this->user->getNumberCourses() . '<br/>
                                 <small>Lớp học</small>
                             </h5>
                        </div>
                        <div class="col-md-4">
                             <h5>' . $this->user->getLassAccess($this->course->getCourseId()) . '<br/>
                                 <small>Current login</small>
                             </h5>
                        </div>
                        <div class="col-md-3">
                             <h5>' . $this->user->getLastIP() . '<br/>
                                 <small>Last IP</small>
                             </h5>
                        </div>
                    </div>
                </div>
             </div>
        </div>';
        return $render;
    }

    public function courseInformation()
    {
        global $CFG, $OUTPUT;
        $render = '<div class="col-lg-6 col-md-6">
                        <div class="card card-user">
                            <div class="image">
                                <img src="assets/img/background.jpg" alt="..."/>    
                                <h4 style="position: absolute;top:10px;left: 10px">' . $this->course->getCourseFullName() . '</h4>
                                <p style="position: absolute;top:80px;left: 10px">' . $this->course->getCourseShortName() . '</p>
                            </div>
                            <div class="content" style="position: relative;top:70px">
                                <div class="author" style="display: block">
                                    <h4 class="title">Teacher: ' . $this->course->printTeachers() . '<br />
                                    </h4>
                                </div>
                                <p class="description text-center">
                                    ' . $this->course->getSummary() . '
                                   
                                </p>
                            </div>
                            <hr>
                            <div class="text-center">
                                <div class="row">
                                    <div class="col-md-3 col-md-offset-1">
                                        <h5>' . $this->course->getNumberStudent() . '<br /><small>Sinh viên</small></h5>
                                    </div>
                                    <div class="col-md-4">
                                        <h5>' . $this->course->getStartDate() . '<br /><small>Ngày bắt đầu</small></h5>
                                    </div>
                                    <div class="col-md-3">
                                        <h5>' . $this->course->getNumberModules() . '<br /><small>Hoạt động</small></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>';
        return $render;
    }

    public function lineChartScript($chartId)
    {
        $section = $this->course->getCurrentSection();
        $label = [];
        if ($this->user->isTeacher($this->context)) {
            $userid = 0;
        } else {
            $userid = $this->user->getUserId();
        }
        for ($i = 1; $i <= $section; $i++) {
            $label [] = $i;
            $viewpost = $this->uet->getViewPostInSection($i, $userid);
            $forum = $this->uet->getForumViewPostInSection($i, $userid);
            $data['view'][] = $viewpost['view'];
            $data['post'][] = $viewpost['post'];
            $data['fview'][] = $forum['view'];
            $data['fpost'][] = $forum['post'];
        }
        $series = '';
        foreach ($data as $key => $d) {
            $series .= json_encode($d) . ',';
        }
        ?>
        <script type="text/javascript">

            var label = <?php echo json_encode($label) ?>;
            var chart = {
                labels: label,
                series: [<?php echo $series ?>]
            };
            var options = {
                axisX: {
                    labelInterpolationFnc: function (value) {
                        return "<?php echo $this->course->getSectionFormat() ?> " + value;
                    }
                }
            };
            Chartist.Line('#<?php echo $chartId ?>', chart, options)
        </script>

        <?php
    }

    public function submitLineChart($chartid)
    {
        $section = $this->course->getCurrentSection();
        $assigns = $this->uet->getAssignmentInSection($section);
        $label = [];
        $series = [];
        foreach ($assigns as $assign){
            $label[] = $assign->name;
            $series[] = floatval(round($this->uet->getSubmissionInAssignment($assign->id)/$this->course->getNumberStudent()*100));
        }
        ?>
        <script type="text/javascript">

            var label = <?php echo json_encode($label) ?>;
            var chart = {
                labels: label,
                series: [<?php echo json_encode($series) ?>]
            };
            Chartist.Line('#<?php echo $chartid ?>', chart)
        </script>
        <?php
    }

    public function pieChartScript($chartId)
    {
        $section = $this->course->getCurrentSection();
        $assigns = $this->uet->getAssignmentSubmissionInSection($section, $this->user->getUserId());
        $data = [intval(round($assigns->submitted / $assigns->total * 100)), intval(round($assigns->not / $assigns->total * 100))];
        $script = '
            Chartist.Pie("#' . $chartId . '", {
                labels: ' . json_encode($data) . ' ,
                series: ' . json_encode($data) . '
                });
        ';
        return $script;
    }

    public function setPage($url)
    {
        global $PAGE, $CFG;
        $PAGE->set_pagelayout('course');
        $PAGE->set_pagetype("course");
        $PAGE->set_title("UET Statistic");
        $PAGE->set_heading('UET Statistic');
        $PAGE->set_context($this->context);
        $PAGE->set_url($url);
        $PAGE->set_cm($this->cm);
        $PAGE->requires->css('/mod/uetanalytics/assets/css/bootstrap.min.css');
        $PAGE->requires->css('/mod/uetanalytics/assets/css/animate.min.css');
        $PAGE->requires->css('/mod/uetanalytics/assets/css/demo.css');
        $PAGE->requires->css('/mod/uetanalytics/assets/css/paper-dashboard.css');
        $PAGE->requires->css('/mod/uetanalytics/assets/css/themify-icons.css');
        $PAGE->requires->jquery();
        $PAGE->requires->js(new moodle_url("$CFG->wwwroot/mod/uetanalytics/assets/js/chartist.min.js"), true);
        $PAGE->requires->js(new moodle_url("$CFG->wwwroot/mod/uetanalytics/assets/js/bootstrap.min.js"));
//        $PAGE->requires->js(new moodle_url("$CFG->wwwroot/mod/uetanalytics/assets/js/demo.js"));
        $PAGE->requires->js(new moodle_url("$CFG->wwwroot/mod/uetanalytics/assets/js/paper-dashboard.js"));

    }
}