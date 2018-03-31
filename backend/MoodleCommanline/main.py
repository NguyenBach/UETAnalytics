#!/usr/bin/python3.6
from UETAnalytics import UETAnalytics
import sys
arg = sys.argv

uet = UETAnalytics()
week = int(arg[1])
data = {
    'view': float(arg[2]),
    'post': float(arg[3]),
    'forumview': float(arg[4]),
    'forumpost': float(arg[5]),
    'successsubmission':float(arg[6])
}
print(uet.predict(week=week, data=data))
