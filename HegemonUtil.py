import sys
import os
import re
import json
import requests
from PIL import Image
import StringIO
import pandas as pd
import seaborn as sns

def sumf(arr):
  s = 0
  for i in arr:
    s += i
  return s

def meanf(arr):
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

def plotBooleanPair(obj, pHash=None):
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
  def hasIH(self):
      return 'indexHeader' in self.hash
  def hasSurv(self):
      return 'survival' in self.hash
  def hasPlatform(self):
      return 'platform' in self.hash
  def hasInfo(self):
      return 'info' in self.hash
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
    for k,n in self.list.iteritems():
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
    self.idhash = {};
    self.namehash = {};
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
    self.idhash = {}
    self.namehash = {}
    f = self.rdataset.getIdx();
    self.readIndexFile(f);

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
    exprFile = self.getExprFile();
    ptr1 = self.getPtr(id);
    if (ptr1 is None):
      return None
    x_arr, h_arr = getX(exprFile, ptr1, 0);
    return getThrData(x_arr, self.start, self.getNum());

  def compareIds(self, id1, id2):
    data1 = self.getExprData(id1);
    data2 = self.getExprData(id2);
    if (data1 is None or data2 is None):
      return 0;
    thr1 = getThrData(data1, self.start, self.getNum());
    thr2 = getThrData(data2, self.start, self.getNum());
    count1 = 0;
    count2 = 0;
    for i in range(self.start, self.end + 1):
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
        print name in self.namehash
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
        if (len(ll) != 4):
            continue;
        id, ptr, p_name, desc = ll;
        lp = p_name.split(" /// ");
        if (val is None):
            self.idhash[id] = [ptr, lp[0].strip(), desc];
            self.namehash[id.upper()] = [id];
            for pn in lp:
              pn = pn.strip().upper();
              if (pn == "" or pn == "---"):
                continue;
              if (pn not in self.namehash):
                self.namehash[pn] = []
              if (id not in self.namehash[pn]):
                self.namehash[pn].append(id);
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
                self.idhash[id] = [ptr, lp[0].upper(), desc];
                self.namehash[id.upper()] = [id];
                for pn in lp:
                  pn = pn.strip().upper();
                  if (pn == "" or pn == "---"):
                      continue;
                  if not (pn in self.namehash):
                    self.namehash[pn] = []
                  if not (id in self.namehash[pn]):
                    self.namehash[pn].append(id);
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

def main():
  inputarr = [1.2, 3, 5.6, 4, 10, 12, 7]
  print "input is: " + ', '.join([str(s) for s in inputarr])
  print "mean is: " + str(meanf(inputarr))
  print "variance is "+ str(variancef(inputarr))
  print "mse is "+ str(msef(inputarr))
  print "standard deviation is " + str(stdevf(inputarr))
  print "fit step is: " + str(fitstep(inputarr))
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
  obj = getHegemonPlots("LK21", "CD96", "CA1")
  print obj
  img = getHegemonImg(obj[0])

if __name__ == "__main__":
  main()

