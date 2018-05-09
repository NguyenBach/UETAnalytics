#!/usr/bin/p
from uetla.UETAnalytics import UETAnalytics
import sys
import os
from os.path import expanduser
arg = sys.argv
filepath = os.path.dirname(os.path.realpath(__file__))
uet = UETAnalytics(filepath)
week = int(arg[1])
data = {
    'view': float(arg[2]),
    'post': float(arg[3]),
    'forumview': float(arg[4]),
    'forumpost': float(arg[5]),
    'successsubmission':float(arg[6])
}
print(uet.predict(week=week, data=data))
