import sys
import os
import re
import json
import requests
from PIL import Image
import StringIO
import pandas as pd
import seaborn as sns
import numpy as np
import bitarray
import math
from matplotlib import pyplot as plt
from lifelines import KaplanMeierFitter

def uniq(mylist):
  used = set()
  unique = [x for x in mylist if x not in used and (used.add(x) or True)]
  return unique

def sumf(arr):
  s = 0
  for i in arr:
    s += i
  return s

def meanf(arr):
  if len(arr) == 0:
    return 0
  else:
    return sum(arr)/len(arr)

def variancef(arr):
  sq = 0.0
  m = meanf(arr)
  sumsq = 0
  for item in arr:
    sumsq += item**2
  return (sumsq/len(arr) - m*m)

def stdevf(arr):
  return variancef(arr) ** 0.5

def msef(arr):
  result = 0
  mean = meanf(arr)
  for item in arr:
    result += (item - mean) ** 2
  return result

def fitstep(arr):
  if len(arr) <= 0:
    return None
  #start = 0    # start and end are indices in arr  
  #end = count - 1
  sseArray = [0 for i in range(len(arr))] 
  sum = sumf(arr)
  mean = meanf(arr)
  sstot = msef(arr)
  count = len(arr)
  count1 = 0
  count2 = len(arr)
  sum1 = 0.0
  sum2 = sum
  sum1sq = 0.0
  sum2sq = sstot
  m1 = 0.0
  m2 = mean
  sse = sum1sq + sum2sq
  
  # loops through the array where index is an integer
  for index in range(count):
    entry = arr[index]
    # checks if element in array exists
    if entry is None:
      sseArray[index] = sse 

    count1 += 1
    count2 -= 1
    
    # checking if the division reaches the beginning so if the end counter reaches the beginning counter
    if count2 == 0:
      sseArray[index] = sstot
      continue;

    tmp = (mean - (entry + sum1)/count1)
    sum1sq = sum1sq + (entry - mean)**2 - tmp**2 * count1 + (count1 - 1) * (mean - m1)**2
    tmp = (mean - (sum2 - entry)/count2)
    sum2sq = sum2sq - (entry - mean)**2 - tmp**2 * count2 + (count2 + 1) * (mean - m2)**2
    sum1 += entry
    sum2 -= entry
    m1 = sum1/count1
    m2 = sum2/count2
    sse = sum1sq + sum2sq
    sseArray[index] = sse
  
  # find the minimum sumsq and its index
  bestSse = min(sseArray)
  bestIndex = sseArray.index(bestSse)

  # find mean of the first part and second part
  m1 = meanf(arr[:bestIndex+1])
  m2 = meanf(arr[bestIndex+1:])
  
  #threshold
  thr = (m1 + m2) /2

  # list reversed or not
  label = 0
  if m1 < m2:
    label = 1
  else:
    label = 2

  statistic = 0
  if bestSse > 0 :
    if count > 4:
      statistic = (sstot - bestSse)/3/(bestSse/(count - 4))
    else:
      statistic = (sstot - bestSse)/2/bestSse

  return {"cutoff": bestIndex+1, "bestSse": bestSse, "sstot": sstot, "statistic" : statistic, "threshold": thr, "label":label, "m1": m1, "m2": m2}

def getX(filename, x, debug):
  if not os.path.isfile(filename):
    print "Can't open file {0} <br>".format(filename);
    exit()
  fp = open(filename, "r")
  header = fp.readline().strip()
  fp.seek(long(x), 0)
  in_x = fp.readline().strip()
  if (debug == 1):
    print "Line 1:<br/>",in_x,":<br>";
  fp.close()
  x_arr = in_x.split("\t")
  h_arr = header.split("\t")
  return (x_arr, h_arr);

def getHash(filename, index=None):
  if index is None:
      index = 0
  if not os.path.isfile(filename):
    print "Can't open file {0} <br>".format(filename);
    exit()
  res = {}
  fp = open(filename, "r")
  for line in fp:
      line = line.strip()
      ll = line.split("\t");
      res[ll[index]] = ll
  return res

def getStepMinerThr(data, start=None, end=None):
  if (start is None):
    start = 0;
  if end is None:
    end = len(data) - 1;
  array = []
  for i in range(start, len(data)):
    if (data[i] is None or data[i] is ""):
      continue;
    array.append(float(data[i]))
  array.sort()
  return fitstep(array);

def getThrData(arr, start = None, length = None):
  if start is None:
    start = 0
  if length is None:
    length = len(arr)
  s_thr = getStepMinerThr(arr, start, start+length-1)
  thr = s_thr["threshold"]
  stat = s_thr["statistic"]
  return [thr, stat, thr-0.5, thr+0.5]

def getHegemonPlots(dbid, gA, gB):
  url = "http://hegemon.ucsd.edu/Tools/explore.php?go=getplotsjson&id=" + \
          dbid + "&A=" + gA + "&B=" + gB
  response = requests.get(url)
  obj = json.loads(response.text)
  return  obj

def getHegemonData(dbid, gA, gB):
  url = "http://hegemon.ucsd.edu/Tools/explore.php?go=getdatajson&id=" + \
          dbid + "&A=" + gA + "&B=" + gB
  response = requests.get(url)
  obj = json.loads(response.text)
  return  obj

def getHegemonThr(dbid, gA, gB):
  url = "http://hegemon.ucsd.edu/Tools/explore.php?go=getthrjson&id=" + \
          dbid + "&A=" + gA + "&B=" + gB
  response = requests.get(url)
  obj = json.loads(response.text)
  return  obj

def getHegemonPatients(dbid, clinical, value):
  url = "http://hegemon.ucsd.edu/Tools/explore.php?go=getPatients&id=" + \
          dbid + "&clinical=" + clinical + "&value=" + value
  response = requests.get(url)
  obj = response.text.split("\n")
  pHash = {}
  for i in range(2, 2+int(obj[0])):
    l = obj[i].split("\t")
    if len(l) < 2:
        continue
    pHash[l[0]] = l[1]
  return  pHash

def getHegemonPatientInfo(dbid):
  url = "http://hegemon.ucsd.edu/Tools/explore.php?go=getpatientinfojson" + \
        "&id=" + dbid
  response = requests.get(url)
  obj = json.loads(response.text)
  return  obj

def getHegemonPatientData(dbid, name):
  hdr = getHegemonPatientInfo(dbid)
  clinical = 0
  if name in hdr:
    clinical = hdr.index(name)
  url = "http://hegemon.ucsd.edu/Tools/explore.php?go=getpatientdatajson" + \
        "&id=" + dbid + "&clinical=" + str(clinical)
  response = requests.get(url)
  obj = json.loads(response.text)
  return  obj

def getHegemonPtr(exprFile, ptr):
  url = "http://hegemon.ucsd.edu/Tools/explore.php?go=getptrjson&file=" + \
          exprFile + "&x=" + ptr
  response = requests.get(url)
  return json.loads(response.text)

def getHegemonImg(obj):
  url = "http://hegemon.ucsd.edu/Tools/explore.php?go=plot&file=" + \
          obj[4] + "&id=" + obj[5] + \
          "&xn=" + obj[6] + "&yn=" + obj[7] + \
          "&x=" + obj[8] + "&y=" + obj[9]
  response = requests.get(url)
  img = Image.open(StringIO.StringIO(response.content))
  return img

def getHegemonDataset(dbid):
  url = "http://hegemon.ucsd.edu/Tools/explore.php?go=getdatasetjson&id=" + \
          dbid
  response = requests.get(url)
  obj = json.loads(response.text)
  return  obj

def plotPair(obj):
  info = getHegemonDataset(obj[5]);
  datax = getHegemonPtr(obj[4], obj[8])
  datay = getHegemonPtr(obj[4], obj[9])
  df = pd.DataFrame()
  df["x"] = pd.to_numeric(pd.Series(datax[1][2:]))
  df["y"] = pd.to_numeric(pd.Series(datay[1][2:]))
  ax = df.plot.scatter(x='x', y='y')
  ax.set_xlabel(obj[6])
  ax.set_ylabel(obj[7])
  ax.set_title("{0} (n = {1})".format(info[1], info[2]))
  return ax

def plotBooleanSelect(obj, pHash=None):
  info = getHegemonDataset(obj[5]);
  thr = getHegemonThr(obj[5], obj[0], obj[2]);
  thash = {}
  for v in thr:
    thash[v[0]] = v
  datax = getHegemonPtr(obj[4], obj[8])
  datay = getHegemonPtr(obj[4], obj[9])
  thrx = thash[str(obj[0])]
  thry = thash[str(obj[2])]
  df = pd.DataFrame()
  if pHash is None:
      df["x"] = pd.to_numeric(pd.Series(datax[1][2:]))
      df["y"] = pd.to_numeric(pd.Series(datay[1][2:]))
  else:
      order = [i for i in range(2, len(datax[0])) if datax[0][i] in pHash]
      val = [datax[1][i] for i in order]
      df["x"] = pd.to_numeric(pd.Series(val))
      val = [datay[1][i] for i in order]
      df["y"] = pd.to_numeric(pd.Series(val))
  ax = df.plot.scatter(x='x', y='y')
  ax.set_xlabel(obj[6])
  ax.set_ylabel(obj[7])
  ax.set_title("{0} (n = {1})".format(info[1], info[2]))
  ax.axhline(y=thry[1], color='r')
  ax.axhline(y=thry[3], color='cyan')
  ax.axhline(y=thry[4], color='cyan')
  ax.axvline(x=thrx[1], color='r')
  ax.axvline(x=thrx[3], color='cyan')
  ax.axvline(x=thrx[4], color='cyan')
  return ax

def plotBooleanPair(obj, pGroups=None):
  info = getHegemonDataset(obj[5]);
  thr = getHegemonThr(obj[5], obj[0], obj[2]);
  thash = {}
  for v in thr:
    thash[v[0]] = v
  datax = getHegemonPtr(obj[4], obj[8])
  datay = getHegemonPtr(obj[4], obj[9])
  thrx = thash[str(obj[0])]
  thry = thash[str(obj[2])]
  w,h = (6.4, 4.8)
  dpi = 100
  fig = plt.figure(figsize=(w,h))
  ax = fig.add_axes([70.0/w/dpi, 54.0/h/dpi, 1-2*70.0/w/dpi, 1-2*54.0/h/dpi])
  if pGroups is None:
    df = pd.DataFrame()
    df["x"] = pd.to_numeric(pd.Series(datax[1][2:]))
    df["y"] = pd.to_numeric(pd.Series(datay[1][2:]))
    #ax = df.plot.scatter(x='x', y='y')
    ax.plot(df["x"], df["y"], ls='None', marker='.', color='blue')
  else:
    for k in range(len(pGroups)):
      df = pd.DataFrame()
      order = pGroups[k][2]
      val = [datax[1][i] for i in order]
      df["x"] = pd.to_numeric(pd.Series(val))
      val = [datay[1][i] for i in order]
      df["y"] = pd.to_numeric(pd.Series(val))
      ax.plot(df["x"], df["y"], ls='None', marker='.', color=pGroups[k][1])
  ax.set_xlabel(obj[6])
  ax.set_ylabel(obj[7])
  ax.set_title("{0} (n = {1})".format(info[1], info[2]))
  ax.axhline(y=thry[1], color='r')
  ax.axhline(y=thry[3], color='cyan')
  ax.axhline(y=thry[4], color='cyan')
  ax.axvline(x=thrx[1], color='r')
  ax.axvline(x=thrx[3], color='cyan')
  ax.axvline(x=thrx[4], color='cyan')
  return ax

def boxplotArray(data, pGroups=None, thr=None, ax=None):
  if ax is None:
    w,h = (6.4, 4.8)
    dpi = 100
    fig = plt.figure(figsize=(w,h))
    ax = fig.add_axes([70.0/w/dpi, 54.0/h/dpi, 1-2*70.0/w/dpi, 1-2*54.0/h/dpi])
  if pGroups is None:
    df = pd.DataFrame()
    df["x"] = pd.to_numeric(pd.Series(data[2:]))
    ax.boxplot(df["x"])
  else:
    bdata = []
    for k in range(len(pGroups)):
      df = pd.DataFrame()
      order = pGroups[k][2]
      val = [data[i] for i in order if data[i] != ""]
      df["x"] = pd.to_numeric(pd.Series(val))
      bdata.append(df["x"])      
    bp = ax.boxplot(bdata, patch_artist=True)
    ax.set_xticklabels([ g[0] for g in pGroups ])
    for k in range(len(pGroups)):
      bp['boxes'][k].set(color = pGroups[k][1])
      bp['boxes'][k].set(facecolor = pGroups[k][1], alpha=0.2)
      bp['fliers'][k].set(color = pGroups[k][1])
      bp['medians'][k].set(color = pGroups[k][1])
    for k in range(2*len(pGroups)):
      bp['whiskers'][k].set(color = pGroups[k/2][1])
      bp['caps'][k].set(color = pGroups[k/2][1])
  ax.set_ylabel(data[1])
  if thr is not None:
    ax.axhline(y=thr[1], color='r')
    ax.axhline(y=thr[3], color='cyan')
    ax.axhline(y=thr[4], color='cyan')
  return ax

def boxplot(obj, pGroups=None):
  info = getHegemonDataset(obj[5]);
  thr = getHegemonThr(obj[5], obj[0], obj[2]);
  thash = {}
  for v in thr:
    thash[v[0]] = v
  datax = getHegemonPtr(obj[4], obj[8])
  datay = getHegemonPtr(obj[4], obj[9])
  thrx = thash[str(obj[0])]
  thry = thash[str(obj[2])]
  w,h = (12, 4.8)
  dpi = 100
  fig = plt.figure(figsize=(w,h))
  ax = plt.subplot(1, 2, 1)
  datax[1][1] = obj[6]
  boxplotArray(datax[1], pGroups, thrx, ax)
  ax.set_title("{0} (n = {1})".format(info[1], info[2]))
  ax = plt.subplot(1, 2, 2)
  datay[1][1] = obj[7]
  boxplotArray(datay[1], pGroups, thry, ax)
  ax.set_title("{0} (n = {1})".format(info[1], info[2]))
  return ax

def survival(time, status, pGroups=None):
  kmf = KaplanMeierFitter()
  if pGroups is None:
    order = [i for i in range(2, len(time)) 
		if time[i] != "" and status[i] != ""]
    t = [float(time[i]) for i in order]
    s = [int(status[i]) for i in order]
    kmf.fit(t, s)
    ax = kmf.plot(color='red')
    return ax
  else:
    ax = None
    groups = [ "" for i in time]
    for k in range(len(pGroups)):
      df = pd.DataFrame()
      order = [i for i in pGroups[k][2]
               if time[i] != "" and status[i] != ""]
      if len(order) <= 0:
          continue
      for i in order:
        groups[i] = k
      t = [float(time[i]) for i in order]
      s = [int(status[i]) for i in order]
      kmf.fit(t, s, label = pGroups[k][0])
      if ax is None:
        ax = kmf.plot(color=pGroups[k][1], ci_show=False, show_censors=True)
      else:
        ax = kmf.plot(ax = ax, color=pGroups[k][1], ci_show=False, show_censors=True)
    order = [i for i in range(len(groups)) if groups[i] != ""]
    if len(order) > 0:
      t = [float(time[i]) for i in order]
      s = [int(status[i]) for i in order]
      g = [int(groups[i]) for i in order]
      from lifelines.statistics import multivariate_logrank_test
      from matplotlib.legend import Legend
      res = multivariate_logrank_test(t, g, s)
      leg = Legend(ax, [], [], title = "p = %.2g" % res.p_value,
                   loc='lower left', frameon=False)
      ax.add_artist(leg);
    return ax

def multivariate(df):
    from lifelines import CoxPHFitter
    cph = CoxPHFitter()
    cph.fit(df, duration_col='time', event_col='status',
            show_progress=True)
    cph.print_summary()  # access the results using cph.summary

def Multivariate(df):
    import rpy2.robjects as ro
    ro.r('library(survival)')
    ro.r("time <- c(" + ",".join(df["time"]) + ")")
    ro.r("status <- c(" + ",".join(df["status"]) + ")")
    columns = []
    for k in df.columns:
      if k == "time":
        continue
      if k == "status":
        continue
      columns.append(k)
      ro.r(k + " <- c(" + ",".join([str(i) for i in df[k]]) + ")")
    ro.r('x <- coxph(Surv(time, status) ~ ' + '+'.join(columns) + ')')
    ro.r('s <- summary(x)')
    print ro.r('s')
    for k in columns:
        ro.r('x <- coxph(Surv(time, status) ~ ' + k + ')')
        ro.r('s <- summary(x)')
        print ro.r('s')

def getCounts(a_high, a_med, b_high, b_med):
    c0 = (~a_high & ~b_high) & ~(a_med | b_med)
    c1 = (~a_high & b_high) & ~(a_med | b_med)
    c2 = (a_high & ~b_high) & ~(a_med | b_med)
    c3 = (a_high & b_high) & ~(a_med | b_med)
    res = [c0.count(), c1.count(), c2.count(), c3.count()]
    return res
def getEnum(c):
    res = [0, 0, 0, 0]
    total = sum(c)
    if total > 0:
        c0, c1, c2, c3 = c
        res[0] = 1.0 * (c0 + c1) * (c0 + c2)/total;
        res[1] = 1.0 * (c1 + c0) * (c1 + c3)/total;
        res[2] = 1.0 * (c2 + c0) * (c2 + c3)/total;
        res[3] = 1.0 * (c3 + c1) * (c3 + c2)/total;
    return res
def getSnum(e, c):
    res = [0, 0, 0, 0]
    total = sum(c)
    if total > 0:
        c0, c1, c2, c3 = c
        e0, e1, e2, e3 = e
        res[0] = (e0 - c0 + 1)/math.sqrt(e0 + 1);
        res[1] = (e1 - c1 + 1)/math.sqrt(e1 + 1);
        res[2] = (e2 - c2 + 1)/math.sqrt(e2 + 1);
        res[3] = (e3 - c3 + 1)/math.sqrt(e3 + 1);
    return res
def getPnum(c):
    res = [0, 0, 0, 0]
    c0, c1, c2, c3 = c
    res[0] = 0.5 * c0 / (c0 + c1 + 1) + 0.5 * c0/(c0 + c2 + 1);
    res[1] = 0.5 * c1 / (c1 + c0 + 1) + 0.5 * c1/(c1 + c3 + 1);
    res[2] = 0.5 * c2 / (c2 + c0 + 1) + 0.5 * c2/(c2 + c3 + 1);
    res[3] = 0.5 * c3 / (c3 + c1 + 1) + 0.5 * c3/(c3 + c2 + 1);
    return res
def getBooleanStats(a_high, a_med, b_high, b_med):
    c = getCounts(a_high, a_med, b_high, b_med)
    e = getEnum(c)
    s = getSnum(e, c)
    p = getPnum(c)
    return [c, e, s, p]
def getBooleanRelationType(bs, sthr, pthr):
    rel = 0
    snum = bs[2]
    pnum = bs[3]
    stats = []
    for i in range(4):
        if (snum[i] > sthr and pnum[i] < pthr):
            if (rel == 0):
                rel = i + 1;
                stats += [snum[i], pnum[i]]
            if (rel == 2 and i == 2):
                rel = 5
                stats += [snum[i], pnum[i]]
            if (rel == 1 and i == 3):
                rel = 6
                stats += [snum[i], pnum[i]]
    return rel, stats
def getBooleanRelations(bs_arr, sthr, pthr):
    res = []
    for k in bs_arr:
        rel, stats = getBooleanRelationType(k[2], sthr, pthr)
        if rel != 0:
            res.append([k[0], k[1], rel] + stats)
    return res

def getThrCode (thr_step, value, code = None):
  if (code is None):
      return value 
  thr = value;
  if (code == "thr1"):
    thr = thr_step[0];
  elif (code == "thr0"):
    thr = thr_step[2];
  elif (code == "thr2"):
    thr = thr_step[3];
  else:
    thr = code;
  return float(thr);


class Dataset:

  def __init__(self, n_id):  
    self.id = n_id
    self.hash = {}
    self.index = 0

  def getID (self):
      return self.id
  
  def has(self, key):
      if key in self.hash:
          return True;
      return False

  def get(self, key):
      if key in self.hash:
          return self.hash[key]
      else:
          return None

  def set(self, key, value):
      self.hash[key] = value

  def setIndex(self, i):
      self.index = i

  def getIndex(self):
      return self.index

  def getNum(self):
    if (self.hasExpr() and os.path.isfile(self.getExpr())):
      fp = open(self.getExpr(), "r")
      head = fp.readline();
      head = head.strip()
      headers = head.split("\t")
      return len(headers) - 2;
    else:
      return 0

  def details(self):
    print '#{0}'.format(self.index)
    print "[{0}]".format(self.id)
    for k in self.hash:
      print "{0} = {1}".format(k, self.hash[k])
    print ""

  def getName(self):
      return self.hash['name']
  def getExpr(self):
      return self.hash['expr']
  def getIdx(self):
      return self.hash['index']
  def getSurv(self):
      return self.hash['survival']
  def getIH(self):
      return self.hash['indexHeader']
  def getPlatform(self):
      return self.hash['platform']
  def getInfo(self):
      return self.hash['info']
  def getThr(self):
      return self.hash['expr'].replace("-expr.txt", "-thr.txt")
  def getBv(self):
      return self.hash['expr'].replace("-expr.txt", "-bv.txt")
  def getVInfo(self):
      return self.hash['expr'].replace("-expr.txt", "-vinfo.txt")
  def hasExpr(self):
      return 'expr' in self.hash
  def hasIh(self):
      return 'indexHeader' in self.hash
  def hasSurv(self):
      return 'survival' in self.hash
  def hasPlatform(self):
      return 'platform' in self.hash
  def hasInfo(self):
      return 'info' in self.hash
  @staticmethod
  def hasFile(f):
      return os.path.isfile(f)
  def hasThr(self):
      return Dataset.hasFile(self.getThr())
  def hasBv(self):
      return Dataset.hasFile(self.getBv())
  def hasVInfo(self):
      return Dataset.hasFile(self.getVInfo())
  def getPre(self):
      return self.hash['expr'].replace("-expr.txt", "")
  def getSource(self):
      return self.hash['source']


class Database:

  def __init__(self, ifile):  
      self.conf_file = ifile;
      self.init();
      self.build();

  def init(self):
    self.env = {}
    self.list = {}

  def build(self):
    file = self.conf_file

    if os.path.isfile(self.conf_file) is False:
        print "Can't open file {0} <br>".format(self.conf_file)
        exit()

    n_id = None
    lset = None
    res = {}
    index = 0;
    f = open(self.conf_file, "r")
    for line in f:
        line = line.strip();
        if line.startswith("["):
            if (n_id is not None and lset is not None):
                res[n_id] = lset;
            n_id = re.sub('^\s*\[(.+)\]\s*$', '\\1', line)
            lset = Dataset(n_id)
            lset.setIndex(index)
            index += 1
        elif (not re.search('^\s*$', line) and n_id is not None ):
            k, v = line.split("=", 1)
            v = v.strip()
            lset.set(k.strip(), v)
        elif (not re.search('^\s*$', line) and n_id is None):
            k, v = line.split("=", 1)
            v = v.strip()
            v = v.replace('"', "")
            self.env[k.strip()] = v;
    f.close()
    if (n_id is not None and lset is not None):
        res[n_id] = lset
    self.list = res
    
  def details(self):
    for k,v in self.env.iteritems():
      print "{0} = {1}".format(k, v);
    print ""
    for k,n in sorted(self.list.iteritems(), key=lambda (k,v): v.getIndex()):
      n.details();

  def getNum(self):
    return len(self.list)
  def getList(self):
    return self.list;
  def getListKey(self, keys):
    res = [];
    for k,n in sorted(self.list.iteritems(), key=lambda (k,v): v.getIndex()):
      keyfound = 1;
      if (n.has("key")):
        lkey = n.get("key");
        keyfound = 0;
        for k in lkey.split(":"):
            if k in keys:
                keyfound = 1;
      if (keyfound == 1):
        res.append(n)
    return res

  def getTitle(self):
    if 'title' in self.env:
        return self.env['title'];
    return "Title";
  def getDataset(self, id):
    return self.list[id];

  def getConf(self):
    return self.conf_file;

class Hegemon:

  def __init__(self, rd):  
    self.rdataset = rd;
    f = self.rdataset.getExpr();
    self.end = 0;
    self.start = 2;
    self.ids = [];
    self.idhash = {};
    self.namehash = {};
    self.thrhash = {};
    self.survhash = {};
    self.survhdrs = [];
    self.survhdrh = {};
    self.headers = [];
    self.fp = None
    if (os.path.isfile(f)):
      self.fp = open(f, "r")
      self.getHeaders(f);
      self.end = len(self.headers) - 1;
    else:
        print "Can't open file {0} <br>".format(f);
        exit()

  def __del__(self):
      if (self.fp is not None):
        self.fp.close()

  def getHeaders(self, f):
    head = self.fp.readline();
    head = head.strip()
    self.headers = head.split("\t")

  def getAllIDs(self):
    return self.idhash.keys();

  def getExprFile(self):
    return self.rdataset.getExpr();

  def getSource(self):
    return self.rdataset.getSource();

  def getSurv(self):
    if (self.rdataset.hasSurv()):
        return self.rdataset.getSurv();
    return None

  def getPre(self):
    return self.rdataset.getPre();

  def init(self):
    self.ids = []
    self.idhash = {}
    self.namehash = {}
    f = self.rdataset.getIdx();
    self.readIndexFile(f);

  def initThr(self):
    if (self.rdataset.hasThr()):
        f = self.rdataset.getThr();
        self.thrhash = Hegemon.readThrFile(f);

  def initSurv(self):
    if (self.rdataset.hasSurv()):
        f = self.rdataset.getSurv();
        self.survhdrs, self.survhash = Hegemon.readSurvFile(f);
        for i in range(len(self.survhdrs)):
            self.survhdrh[self.survhdrs[i]] = i

  def initPlatform(self):
      if (self.rdataset.hasPlatform()):
          f = self.rdataset.getPlatform();
          self.readPlatformFile(f);

  def getNum(self):
    return self.end-self.start + 1;

  def getStart(self):
    return self.start;

  def getEnd(self):
    return self.end;

  def aRange(self):
    return range(self.start, self.end + 1)

  def getPtr(self, id):
    if (id in self.idhash):
        return self.idhash[id][0];
    if id.upper() in self.namehash:
        id = self.namehash[id.upper()][0];
        if (id in self.idhash):
            return self.idhash[id][0];
    return None

  def getName(self, id):
    if (id in self.idhash):
        return self.idhash[id][1];
    if id.upper() in self.namehash:
        id = self.namehash[id.upper()][0];
        if (id in self.idhash):
            return self.idhash[id][1];
    return None

  def getSimpleName(self, id):
    name = self.getName(id)
    name = name.split(":")[0]
    name = name.split(" /// ")[0]
    return name;

  def getDesc(self, id):
    if (id in self.idhash):
        return self.idhash[id][2];
    if id.upper() in self.namehash:
        id = self.namehash[id.upper()][0];
        if (id in self.idhash):
            return self.idhash[id][2];
    return None


  def getExprData(self, id):
    exprFile = self.getExprFile();
    ptr1 = self.getPtr(id);
    if (ptr1 is None):
      return None
    x_arr, h_arr = getX(exprFile, ptr1, 0);
    return x_arr;

  def getThrData(self, id):
    if id in self.thrhash:
        return self.thrhash[id]
    exprFile = self.getExprFile();
    ptr1 = self.getPtr(id);
    if (ptr1 is None):
      return None
    x_arr, h_arr = getX(exprFile, ptr1, 0);
    return getThrData(x_arr, self.start, self.getNum());

  def getSurvData(self, index):
    res = ["" for i in self.headers];
    res[0] = index
    res[1] = self.survhdrs[index]
    if self.survhash:
        for i in self.aRange():
            arr = self.headers[i]
            if arr in self.survhash:
                if index < len(self.survhash[arr]):
                  res[i] = self.survhash[arr][index]
    return res

  def getSurvName(self, name):
    return self.getSurvData(self.survhdrh[name])

  def compareIds(self, id1, id2):
    data1 = self.getExprData(id1);
    data2 = self.getExprData(id2);
    if (data1 is None or data2 is None):
      return 0;
    thr1 = getThrData(data1, self.start, self.getNum());
    thr2 = getThrData(data2, self.start, self.getNum());
    count1 = 0;
    count2 = 0;
    for i in self.aRange():
        if (re.search('^\s*$', data1[i])):
            continue
        if (re.search('^\s*$', data2[i])):
            continue
        if (data1[i] < thr1[3]):
            continue
        if (data2[i] < thr2[3]):
            continue
        if (data1[i] >= data2[i]):
            count1 += 1
        else:
            count2 += 1
    if (count1 == count2):
      return 0;
    if (count1 < count2):
      return +1
    return -1

  def getIDs(self, name):
    res = {}
    genes = re.split("\s+", name);
    for g in genes:
      name = g.strip()
      if (name in self.idhash):
        res[name] = name;
      if (name.upper() in self.namehash):
        for id in self.namehash[name.upper()]:
          res[id] = name;
    if (len(res) == 0):
      f = self.rdataset.getIdx();
      self.readIndexFile(f, name);
      for g in genes:
        name = g.strip()
        #print name in self.namehash
        if name in self.namehash:
          res[name] = name;
        if (name.upper() in self.namehash):
          for id in self.namehash[name.upper()]:
            res[id] = name;
    return res;


  def getBestID (self, l1):
    if (len(l1) <= 0):
      return None
    if (len(l1) == 1):
      return l1[0];
    l2 = sorted(l1, cmp=self.compareIds);
    return l2[0];

  def readIndexFile(self, f, val = None):
    if not os.path.isfile(f):
      print "Can't open file {0} <br>".format(f);
      exit()
    fp = open(f, "r")
    genes = [];
    if (val is not None):
        genes = re.split("\s+", val);
    line = fp.readline()
    index = 0;
    for line in fp:
        line = line.strip();
        ll = line.split("\t");
        if (len(ll) == 3):
            ll.append("")
        if (len(ll) != 4):
            continue;
        id1, ptr, p_name, desc = ll;
        lp = p_name.split(" /// ");
        if (val is None):
            if id1 not in self.idhash:
                self.ids.append(id1)
            self.idhash[id1] = [ptr, lp[0].strip(), desc];
            self.namehash[id1.upper()] = [id1];
            for pn in lp:
              pn = pn.strip().upper();
              if (pn == "" or pn == "---"):
                continue;
              if (pn not in self.namehash):
                self.namehash[pn] = []
              if (id not in self.namehash[pn]):
                self.namehash[pn].append(id1);
            if (index >= 100000):
              break;
            index += 1
        else:
            for g in genes:
              name = g.strip()
              found = 0;
              if (id == name):
                found = 1;
              for pn in lp:
                pn = pn.strip().upper();
                if (pn == "" or pn == "---"):
                  continue;
                if pn == name.upper():
                  found = 1;

              if (found == 1):
                if id1 not in self.idhash:
                    self.ids.append(id1)
                self.idhash[id1] = [ptr, lp[0].upper(), desc];
                self.namehash[id1.upper()] = [id1];
                for pn in lp:
                  pn = pn.strip().upper();
                  if (pn == "" or pn == "---"):
                      continue;
                  if not (pn in self.namehash):
                    self.namehash[pn] = []
                  if not (id in self.namehash[pn]):
                    self.namehash[pn].append(id1);
    fp.close();


  def readPlatformFile(self, f):
    if not os.path.isfile(f):
      print "Can't open file {0} <br>".format(f);
      exit()
    fp = open(f, "r")
    line = fp.readline()
    for line in fp:
        line = line.strip();
        ll = line.split("\t");
        id = ll[0];
        for i in range(len(ll)):
          if (i == 2):
            continue;
          p_name = ll[i];
          lp = p_name.split(" /// ");
          for pn in lp:
            pn = pn.strip().upper();
            if (pn == "" or pn == "---"):
                continue;
            if (pn not in self.namehash):
              self.namehash[pn] = []
            if (id not in self.namehash[pn]):
              self.namehash[pn].append(id);
    fp.close();

  @staticmethod
  def readThrFile(f):
    if not os.path.isfile(f):
      print "Can't open file {0} <br>".format(f);
      exit()
    fp = open(f, "r")
    thrhash = {}
    for line in fp:
        line = line.strip();
        ll = line.split("\t");
        id = ll[0];
        thrhash[id] = [float(i) for i in ll[1:]]
    fp.close();
    return thrhash

  @staticmethod
  def readSurvFile(f):
    if not os.path.isfile(f):
      print "Can't open file {0} <br>".format(f);
      exit()
    fp = open(f, "r")
    head = fp.readline()
    head = head.strip()
    headers = head.split("\t")
    survhash = {}
    for line in fp:
        line = line.strip();
        ll = line.split("\t");
        id = ll[0];
        survhash[id] = ll
    fp.close();
    return (headers, survhash)

  @staticmethod
  def matchWords(val, desc):
    res = 1;
    for w in val.split(" "):
      if (not re.search(w, desc)):
        res = 0;
    return res;

  @staticmethod
  def searchIndexFile(f, val):
    if not os.path.isfile(f):
      print "Can't open file {0} <br>".format(f);
      exit()
    fp = open(f, "r")
    genes = [];
    if (val is not None):
        genes = re.split("\s+", val);

    line = fp.readline()
    index = 0;
    namehash = {}
    for line in fp:
        line = line.strip();
        ll = line.split("\t");
        if (len(ll) != 4):
            continue;
        id, ptr, p_name, desc = ll;
        if p_name not in namehash:
          if re.search(val, p_name):
            print p_name
            namehash[p_name] = 1
            index += 1
        if (index >= 10):
          break;
    if (index < 10):
      fp.seek(0, 0)
      line = fp.readline()
      for line in fp:
        line = line.strip();
        ll = line.split("\t");
        if (len(ll) != 4):
            continue;
        id, ptr, p_name, desc = ll;
        if p_name not in namehash:
          if Hegemon.matchWords(val, desc):
            print ": ".join([p_name, desc])
            namehash[p_name] = 1
            index += 1
        if (index >= 10):
          break;
    fp.close();

  def printSuggest(self, val):
    idx = self.rdataset.getIdx();
    Hegemon.searchIndexFile(idx, val);

  def readID(self, x):
    fp = self.fp;
    fp.seek(x, 0);
    id = fp.readline(1024).split("\t")[0]
    return id;

  def getTopGenes(self, order=None):
    if (order is None):
      order = self.aRange()
    fp = self.fp;
    fp.seek(0, 0);
    h = fp.readline();
    res = []
    for line in fp:
      line = line.strip();
      ll = line.split("\t")
      e = np.array([float(ll[i]) for i in order])
      df = pd.DataFrame()
      df['a'] = e
      res.append([ll[0], self.getSimpleName(ll[0]), np.mean(e), np.std(e), \
              np.percentile(e, 75) - np.percentile(e, 25), df['a'].mad() ])
    res.sort(key=lambda x: x[5], reverse=True)
    return res

  def getCorrelation(self, id1, id2, order=None):
    ref = self.getExprData(id1)
    ex = self.getExprData(id2)
    if (order is None):
      order = self.aRange()
    refe = np.array([float(ref[i]) for i in order])
    e = np.array([float(ex[i]) for i in order])
    corr = np.corrcoef(e, refe)
    return corr[0][1]

  def getCorrelations(self, id1, order=None):
    ref = self.getExprData(id1)
    if (order is None):
      order = self.aRange()
    fp = self.fp;
    fp.seek(0, 0);
    h = fp.readline();
    res = []
    refe = np.array([float(ref[i]) for i in order])
    for line in fp:
      line = line.strip();
      ll = line.split("\t")
      e = np.array([float(ll[i]) for i in order])
      corr = np.corrcoef(e, refe)
      res.append([ll[0], self.getSimpleName(ll[0]), corr[0][1]])
    return res

  def getBitVector(self, low, high, arr=None):
    a_high = bitarray.bitarray(self.getNum())
    a_med = bitarray.bitarray(self.getNum())
    a_high.setall(False)
    a_med.setall(True)
    if arr is not None:
        low = list(set(low) & set(arr))
        high = list(set(high) & set(arr))
    for i in high:
        a_med[i - self.start] = 0
        a_high[i - self.start] = 1
    for i in low:
        a_med[i - self.start] = 0
    return a_high, a_med

  def getBooleanRelationsBv(self, a_high, a_med):
    bvfile = self.rdataset.getBv();
    fp = open(bvfile, "r")
    head = fp.readline()
    res = []
    for line in fp:
        line = line.strip()
        ll = line.split("\t")
	b_high = bitarray.bitarray(ll[2].replace("1", "0").replace("2", "1"))
        b_med = bitarray.bitarray(ll[2].replace("2", "0"))
        bs = getBooleanStats(a_high, a_med, b_high, b_med)
        res.append([ll[0], self.getSimpleName(ll[0]), bs])
    fp.close()
    return res

  def getArraysThr (self, id1, thr = None, type1 = None):
    res = []
    expr = self.getExprData(id1);
    thr_step = self.getThrData(id1);
    thr = getThrCode(thr_step, thr_step[0], thr);
    for i in self.aRange():
      if (thr is None):
         res.append(i)
      elif (expr[i] == ""):
         continue
      elif (type1 == "hi" and float(expr[i]) >= thr):
         res.append(i)
      elif (type1 == "lo" and float(expr[i]) < thr):
         res.append(i)
      elif (type1 is not None and type1 != "lo" and type1 != "hi" \
              and float(expr[i]) >= thr and float(expr[i]) <= float(type1)): 
         res.append(i)
    return res

  def getArraysAll (self, *data):
    res = self.aRange()
    for i in range(0, len(data), 3):
      r = self.getArraysThr(data[i], data[i+1], data[i+2])
      res = list(set(res) & set(r))
    return res;

  def getBitVectorID(self, id1, arr=None):
    low = self.getArraysAll(id1, "thr0", "lo")
    high = self.getArraysAll(id1, "thr2", "hi")
    return self.getBitVector(low, high, arr)

  def getBooleanRelation(self, id1, id2, arr=None):
    a_high, a_med = self.getBitVectorID(id1, arr)
    b_high, b_med = self.getBitVectorID(id2, arr)
    bs = getBooleanStats(a_high, a_med, b_high, b_med)
    return bs

  def getBooleanRelations(self, id1, arr=None):
    a_high, a_med = self.getBitVectorID(id1, arr)
    bvfile = self.rdataset.getBv();
    fp = open(bvfile, "r")
    head = fp.readline()
    res = []
    for line in fp:
        line = line.strip()
        ll = line.split("\t")
	b_high = bitarray.bitarray(ll[2].replace("1", "0").replace("2", "1"))
        b_med = bitarray.bitarray(ll[2].replace("2", "0"))
        bs = getBooleanStats(a_high, a_med, b_high, b_med)
        res.append([ll[0], self.getSimpleName(ll[0]), bs])
    fp.close()
    return res

def test1():
  inputarr = [1.2, 3, 5.6, 4, 10, 12, 7]
  print "input is: " + ', '.join([str(s) for s in inputarr])
  print "mean is: " + str(meanf(inputarr))
  print "variance is "+ str(variancef(inputarr))
  print "mse is "+ str(msef(inputarr))
  print "standard deviation is " + str(stdevf(inputarr))
  print "fit step is: " + str(fitstep(inputarr))

def test2():
  db = Database("explore.conf")
  keys = { "bm" : 1, "leukemia" : 1 }
  for n in db.getListKey(keys):
    id = n.getID();
    h = Hegemon(n);
    num = h.getNum();
    if (num > 0):
      print " ".join([id, n.getName(), str(num)])
  h = Hegemon(db.getDataset("LK21"))
  h.init()
  h.initPlatform()
  print h.getNum()
  id =  h.getIDs("CD96").keys()[0]
  #h.printSuggest("cell division")
  print id, h.getName(id)
  print h.getPre()
  print h.getIDs("CA1")
  print h.getBestID(h.getIDs("CA1").keys())
  print h.getBestID(h.getIDs("CD96 FLT3").keys())

def test3():
  obj = getHegemonPlots("LK21", "CD96", "CA1")
  print obj
  img = getHegemonImg(obj[0])
  obj = getHegemonPatientInfo("LK21")
  print obj
  obj = getHegemonPatientData("LK21", 'c Title')

def test4():
  db = Database("explore.conf")
  h = Hegemon(db.getDataset("PLP7"))
  h.init()
  h.initPlatform()
  print h.rdataset.hasThr()
  h.initSurv()
  print h.getSurvName("c clinical condition")

def main():
  #test1()
  #test2()
  #test3()
  test4()

if __name__ == "__main__":
  main()

