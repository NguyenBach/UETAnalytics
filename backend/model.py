#!/usr/bin/python3
from uetla.UETAnalytics import UETAnalytics
import sys
import os
arg = sys.argv
filepath = os.path.dirname(os.path.realpath(__file__))
uet = UETAnalytics(filepath)
uet.createModel()