
import matplotlib
matplotlib.use('agg')
import re
from pylab import *
    
data = {}
f = open('tmpdir/test.data', 'r')
for line in f:
    t = line.split(' ');
    t[2] = t[2].strip();
    if (t[2] not in data):
      data[t[2]] = [[], []]
    data[t[2]][0] += [float(t[0])];
    data[t[2]][1] += [float(t[1])];
fig = figure(figsize=(6.4,4.8))
ax = fig.add_axes([70.0/640, 54.0/480, 1-2*70.0/640, 1-2*54.0/480])
colors = data.keys()
colors.sort(key=lambda t:-len(data[t][0]))
for c in colors:
    ax.plot(data[c][0],data[c][1], color=c, ls='None', marker='+', mew=1.1, ms=4, mec=c)
ax.axis([3.348288, 13.357413, 2.661444, 12.128079])
ax.set_xlabel('1626996_s_at:  br', fontsize=10)
ax.set_ylabel('1628224_a_at:  E23', fontsize=10)
fig.savefig('tmpdir/test.png', dpi=100)
