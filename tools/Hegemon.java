package tools;

import java.io.*;
import java.util.*;
import java.util.stream.*;

class Hegemon {
  String pre;
  PrintStream out = System.out;
  String[] headers;
  int start;
  int end;

  public Hegemon(String p) {
    pre = p;
    start = 2;
    headers = getHeaders();
    end = headers.length - 1;
  }

  public boolean isExpr() {
    File f = new File(pre);
    if(f.exists() && !f.isDirectory()) { 
      return true;
    }
    return false;
  }

  public boolean hasExpr() {
    File f = new File(getExpr());
    if(f.exists() && !f.isDirectory()) { 
      return true;
    }
    return false;
  }

  public String getExpr() {
    return pre + "-expr.txt";
  }

  public boolean hasBv() {
    File f = new File(getBv());
    if(f.exists() && !f.isDirectory()) { 
      return true;
    }
    return false;
  }

  public String getBv() {
    return pre + "-bv.txt";
  }

  public boolean hasInfo() {
    File f = new File(getInfo());
    if(f.exists() && !f.isDirectory()) { 
      return true;
    }
    return false;
  }

  public String getInfo() {
    return pre + "-info.txt";
  }

  public String getExprFile() {
    String exprFile;
    if (isExpr()) {
      return pre;
    }
    else {
      if (hasExpr()) {
        return getExpr();
      }
    }
    return null;
  }

  public String[] getHeaders() {
    try {
      String exprFile = getExprFile();
      if (exprFile == null) {
        return null;
      }
      FileReader fileReader = new FileReader(exprFile);
      BufferedReader bufferedReader = new BufferedReader(fileReader);
      String line = bufferedReader.readLine();
      String[] result = line.split("\\t", -2);
      bufferedReader.close(); 
      return result;
    }
    catch (Exception e) {
      return null;
    }
  }

  public String[] get2Lines(String bvFile, String id1, String id2) throws FileNotFoundException, IOException {
    String line;
    String[] res = new String[2];
    FileReader fileReader = 
      new FileReader(bvFile);

    // Always wrap FileReader in BufferedReader.
    BufferedReader bufferedReader = 
      new BufferedReader(fileReader);

    int count = 0;
    while((line = bufferedReader.readLine()) != null) {
      if(line.startsWith(id1 + "\t")) {
        res[0] = line;
        count++;
      }
      if(line.startsWith(id2 + "\t")) {
        res[1] = line;
        count++;
      }
      if (count == 2) {
        break;
      }
    }   

    // Always close files.
    bufferedReader.close();         
    return res;
  }

  public String getLine(String bvFile, String id) throws FileNotFoundException, IOException {
    String line;
    FileReader fileReader = 
      new FileReader(bvFile);

    // Always wrap FileReader in BufferedReader.
    BufferedReader bufferedReader = 
      new BufferedReader(fileReader);

    while((line = bufferedReader.readLine()) != null) {
      if(line.startsWith(id + "\t")) {
        return line;
      }
    }   

    // Always close files.
    bufferedReader.close();         
    return null;
  }

    /**
   *  Bitvector file format
   *    0 - low
   *    1 - intermediate
   *    2 - high
   *    Argument type == 0 : returns bitSet where char is 2
   *    Argument type == 1 : returns bitSet where char is not 1 and not blank
   */
  public static BitSet stringToBitSet(String str, int type) {
    BitSet res = new BitSet(str.length());
    //System.out.println(str.length());
    for (int i =0; i < str.length(); i++) {
      char c = str.charAt(i);
      res.clear(i);
      if (type == 0 && c == '2') {
        res.set(i);
      }
      if (type == 1 && !(c == '1' || c == ' ')) {
        res.set(i);
      }
    }
    //System.out.println(res.size());
    return res;
  }

  public boolean haveGoodDynamicRange(int num, BitSet va_thr) {
    int outside = va_thr.cardinality();
    if (num > (3 * outside)) {
      return false;
    }
    else {
      return true;
    }
  }

  public void getBnum(int[] res, 
      BitSet a, BitSet a_thr, BitSet b, BitSet b_thr, BitSet groups) {
    res[0] = res[1] = res[2] = res[3] = 0;
    if (a.length() == 0 || b.length() == 0) {
      return;
    }
    BitSet thrBits = (BitSet) a_thr.clone();
    thrBits.and(b_thr);
    if (groups != null) { thrBits.and(groups); }
    BitSet tmp = (BitSet) thrBits.clone();
    BitSet v1 = (BitSet) a.clone();
    v1.or(b);
    tmp.andNot(v1);
    int c0 = tmp.cardinality();
    tmp = (BitSet) thrBits.clone();
    v1 = (BitSet) b.clone();
    v1.andNot(a);
    tmp.and(v1);
    int c1 = tmp.cardinality();
    tmp = (BitSet) thrBits.clone();
    v1 = (BitSet) a.clone();
    v1.andNot(b);
    tmp.and(v1);
    int c2 = tmp.cardinality();
    tmp = (BitSet) thrBits.clone();
    v1 = (BitSet) a.clone();
    v1.and(b);
    tmp.and(v1);
    int c3 = tmp.cardinality();

    res[0] = c0;
    res[1] = c1;
    res[2] = c2;
    res[3] = c3;
  }

  public void getEstNum(double[] res, int[] bnum) {
    res[0] = res[1] = res[2] = res[3] = 0.0;
    int c0 = bnum[0];
    int c1 = bnum[1];
    int c2 = bnum[2];
    int c3 = bnum[3];
    int total = c0 + c1 + c2 + c3;
    if (total <= 0) {
      return;
    }
    res[0] = (c0 + c1) * (c0 + c2)/total;
    res[1] = (c1 + c0) * (c1 + c3)/total;
    res[2] = (c2 + c0) * (c2 + c3)/total;
    res[3] = (c3 + c1) * (c3 + c2)/total;
  }

  public void getSnum(double[] res, int[] bnum, double[] estnum) {
    res[0] = res[1] = res[2] = res[3] = 1.0;
    int c0 = bnum[0];
    int c1 = bnum[1];
    int c2 = bnum[2];
    int c3 = bnum[3];
    int total = c0 + c1 + c2 + c3;
    if (total <= 0) {
      return;
    }
    double e0 = estnum[0];
    double e1 = estnum[1];
    double e2 = estnum[2];
    double e3 = estnum[3];
    res[0] = (e0 - c0 + 1)/Math.sqrt(e0 + 1);
    res[1] = (e1 - c1 + 1)/Math.sqrt(e1 + 1);
    res[2] = (e2 - c2 + 1)/Math.sqrt(e2 + 1);
    res[3] = (e3 - c3 + 1)/Math.sqrt(e3 + 1);
  }

  public void getPnum(double[] res, int[] bnum) {
    int c0 = bnum[0];
    int c1 = bnum[1];
    int c2 = bnum[2];
    int c3 = bnum[3];
    res[0] = 0.5 * c0 / (c0 + c1 + 1) + 0.5 * c0/(c0 + c2 + 1);
    res[1] = 0.5 * c1 / (c1 + c0 + 1) + 0.5 * c1/(c1 + c3 + 1);
    res[2] = 0.5 * c2 / (c2 + c0 + 1) + 0.5 * c2/(c2 + c3 + 1);
    res[3] = 0.5 * c3 / (c3 + c1 + 1) + 0.5 * c3/(c3 + c2 + 1);
  }

  public static String strJoin(String sSep, int[] aArr) {
    StringBuilder sbStr = new StringBuilder();
    for (int i = 0, il = aArr.length; i < il; i++) {
      if (i > 0)
        sbStr.append(sSep);
      sbStr.append(aArr[i]);
    }
    return sbStr.toString();
  }

  public static String strJoin(String sSep, double[] aArr) {
    StringBuilder sbStr = new StringBuilder();
    for (int i = 0, il = aArr.length; i < il; i++) {
      if (i > 0)
        sbStr.append(sSep);
      sbStr.append(String.format("%1$.3g", aArr[i]));
    }
    return sbStr.toString();
  }

  public static String infoJSON(String[] aArr) {
    String sSep = ",";
    StringBuilder sbStr = new StringBuilder();
    sbStr.append("[");
    for (int i = 0, il = aArr.length; i < il; i++) {
      if (i > 0)
        sbStr.append(sSep);
      if (i < 2) 
        sbStr.append(String.format("\"%s\"", aArr[i]));
      else
        sbStr.append(String.format("%s", aArr[i]));
    }
    sbStr.append("]");
    return sbStr.toString();
  }

  public Set<String> getFilter() throws FileNotFoundException, Exception {
    if (!hasInfo()) {
      return null;
    }
    String infoFile = getInfo();
    HashSet<String> res = new HashSet<String>();
    String line;
    ArrayList<String> idlist = new ArrayList<String>();
    ArrayList<Double> drlist = new ArrayList<Double>();
    ArrayList<Double> sdlist = new ArrayList<Double>();
    FileReader fileReader = new FileReader(infoFile);
    BufferedReader bufferedReader = new BufferedReader(fileReader);
    line = bufferedReader.readLine();
    while((line = bufferedReader.readLine()) != null) {
      String[] result = line.split("\\t", -2);
      if (result.length < 9) {
        continue;
      }
      double dr = Double.parseDouble(result[7]) - Double.parseDouble(result[6]);
      double sd = Double.parseDouble(result[8]);
      idlist.add(result[0]);
      drlist.add(new Double(dr));
      sdlist.add(new Double(sd));
    }
    bufferedReader.close(); 
    double[] drdata = new double[drlist.size()];
    double[] sddata = new double[sdlist.size()];
    for (int x=0; x<idlist.size(); x++) {
        drdata[x] = drlist.get(x);
        sddata[x] = sdlist.get(x);
    }
    Arrays.sort(drdata);
    Arrays.sort(sddata);
    double drthr = fitStep(drdata, 0, drdata.length-1);
    double sdthr = fitStep(sddata, 0, sddata.length-1);
    //out.println(drthr);
    //out.println(sdthr);
    //double[] data = {1, 1, 1, 2, 3, 1, 4, 5, 4, 6, 4, 5};
    //double thr = fitStep(data, 0, data.length-1);
    //out.println(thr);
    for (int x=0; x<idlist.size(); x++) {
        if (drlist.get(x) > drthr && sdlist.get(x) > sdthr) {
            res.add(idlist.get(x));
        }
    }
    return res;
  }

  public void printHiDr() {
    String infoFile = getInfo();
    try {
      Set<String> keys = getFilter();
      for (String id : keys) {
        out.println(id);
      }
    }
    catch(FileNotFoundException ex) {
      out.println( "Unable to open file '" + infoFile + "'");
    }
    catch(Exception ex) {
      ex.printStackTrace();
    }
  }

  public void printBalanced(String singleCutoff, String singleThr) {
    if (!hasBv()) {
      return;
    }
    String bvFile = getBv();
    String line;
    try {
      double single_threshold_ = Double.parseDouble(singleThr);
      int single_cutoff_ = Integer.parseInt(singleCutoff);
      FileReader fileReader = new FileReader(bvFile);
      BufferedReader bufferedReader = new BufferedReader(fileReader);
      while((line = bufferedReader.readLine()) != null) {
        String[] result = line.split("\\t", -2); // -2 : Don't discard trailing nulls
        if (result.length < 2) {
          continue;
        }
        int numArr = result[2].length();
        BitSet vb = stringToBitSet(result[2], 0);
        BitSet vb_thr = stringToBitSet(result[2], 1);
        if (!haveGoodDynamicRange(numArr, vb_thr)) {
          continue;
        }
        int outside = vb_thr.cardinality();
        int c1 = vb.cardinality();
        BitSet tmp = (BitSet) vb_thr.clone();
        tmp.andNot(vb);
        int c0 = tmp.cardinality();
        int total = c0 + c1;
        if (total <= 0) {
          continue;
        }
        double p = (c0/(c0+c1+0.0));
        if (c0 < single_cutoff_ && p < single_threshold_ ) {
          continue;
        }
        if (c1 < single_cutoff_ && (1-p) < single_threshold_ ) {
          continue;
        }
        out.println(result[0] + "\t" + numArr + "\t" + outside +
            "\t" + total + "\t" + c0 + "\t" + c1 + "\t" + p);
      }   
      bufferedReader.close();         
    }
    catch(FileNotFoundException ex) {
      out.println( "Unable to open file '" + bvFile + "'");
    }
    catch(Exception ex) {
      ex.printStackTrace();
    }
  }

  public BitSet getGroups(String listFile)  {
    if (listFile == null) {
      return null;
    }
    try {
      String exprFile = getExprFile();
      if (exprFile == null) {
        return null;
      }
      String line;
      FileReader fileReader = new FileReader(listFile);
      BufferedReader bufferedReader = new BufferedReader(fileReader);
      HashSet<String> idlist = new HashSet<String>();
      while((line = bufferedReader.readLine()) != null) {
        String[] result = line.split("\\t", -2);
        idlist.add(result[0]);
      }
      BitSet res = new BitSet(headers.length - start);
      //System.out.println(str.length());
      for (int i =start; i <= end; i++) {
        res.clear(i - start);
        if (idlist.contains(headers[i])) {
          res.set(i - start);
        }
      }
      return res;
    }
    catch (Exception e) {
      return null;
    }
  }

  public void printBoolean(String id, String listFile, Set<String> keys) {
    if (!hasBv()) {
      return;
    }
    String bvFile = getBv();
    String line;
    try {
      BitSet groups = getGroups(listFile);
      String line1 = getLine(bvFile, id);
      if (line1 == null) {
        return;
      }
      String[] result1 = line1.split("\\t", -2); // -2 : Don't discard trailing nulls
      if (result1.length < 2) {
        return;
      }
      BitSet va = stringToBitSet(result1[2], 0);
      BitSet va_thr = stringToBitSet(result1[2], 1);
      // FileReader reads text files in the default encoding.
      FileReader fileReader = 
        new FileReader(bvFile);

      // Always wrap FileReader in BufferedReader.
      BufferedReader bufferedReader = 
        new BufferedReader(fileReader);

      int[] bnum = new int[4];
      double[] estnum = new double[4];
      double[] snum = new double[4];
      double[] pnum = new double[4];

      while((line = bufferedReader.readLine()) != null) {
        String[] result = line.split("\\t", -2); // -2 : Don't discard trailing nulls
        if (result.length < 2) {
          continue;
        }
        if (keys != null && !keys.contains(result[0])) {
          continue;
        }
        int numArr = result[2].length();
        BitSet vb = stringToBitSet(result[2], 0);
        BitSet vb_thr = stringToBitSet(result[2], 1);
        if (!haveGoodDynamicRange(numArr, vb_thr)) {
          continue;
        }
        getBnum(bnum, va, va_thr, vb, vb_thr, groups);
        getEstNum(estnum, bnum);
        getSnum(snum, bnum, estnum);
        getPnum(pnum, bnum);
        out.println(id + "\t" + result[0] + "\t" + strJoin("\t", bnum) + "\t" +
            strJoin("\t", snum) + "\t" + strJoin("\t", pnum));
      }   

      // Always close files.
      bufferedReader.close();         
    }
    catch(FileNotFoundException ex) {
      out.println( "Unable to open file '" + bvFile + "'");
    }
    catch(Exception ex) {
      ex.printStackTrace();
    }
  }

  public void printBoolean(String id, String listFile) {
    if (!hasBv()) {
      return;
    }
    String infoFile = getInfo();
    try {
      Set<String> keys = getFilter();
      printBoolean(id, listFile, keys);
    }
    catch(FileNotFoundException ex) {
      out.println( "Unable to open file '" + infoFile + "'");
    }
    catch(Exception ex) {
      ex.printStackTrace();
    }
  }

  public void printBoolean(String id) {
    printBoolean(id, null);
  }

  public void printBooleanFile(String idfile, String listFile) {
    if (!hasBv()) {
      return;
    }
    String line;
    try {
      HashSet<String> keys = new HashSet<String>();
      FileReader fileReader = new FileReader(idfile);
      // Always wrap FileReader in BufferedReader.
      BufferedReader bufferedReader = 
        new BufferedReader(fileReader);

      while((line = bufferedReader.readLine()) != null) {
        String[] result = line.split("\\t", -2); // -2 : Don't discard trailing nulls
        if (result.length < 1) {
          continue;
        }
        keys.add(result[0]);
      }   
      // Always close files.
      bufferedReader.close();         
      for (String id : keys) {
        System.err.println(id);
        printBoolean(id, listFile, keys);
      }
    }
    catch(FileNotFoundException ex) {
      out.println( "Unable to open file '" + idfile + "'");
    }
    catch(Exception ex) {
      ex.printStackTrace();
    }
  }

  public void printBooleanFile(String idfile) {
    printBooleanFile(idfile, null);
  }

  public static double min(double[] data, int start, int end) {
    double res = Double.MAX_VALUE;
    for (int i = start; i <= end; i++) {
      if (!Double.isNaN(data[i])) {
        if (res > data[i]) {
          res = data[i];
        }
      }
    }
    return res;
  }

  public static double sum(double[] data, int start, int end) {
    double sum = 0;
    for (int i = start; i <= end; i++) {
      if (!Double.isNaN(data[i])) {
        sum += data[i];
      }
    }
    return sum;
  }

  public static double mean(double[] data, int start, int end) {
    double sum = 0;
    int count = 0;
    for (int i = start; i <= end; i++) {
      if (!Double.isNaN(data[i])) {
        sum += data[i];
        count ++;
      }
    }
    if (count <= 1) {
      return sum;
    }
    return sum/count;
  }

  public static double mse(double[] data, int start, int end) {
    double m = mean(data, start, end);
    double sum = 0;
    for (int i = start; i <= end; i++) {
      if (!Double.isNaN(data[i])) {
        sum += (m - data[i]) * (m - data[i]);
      }
    }
    return sum;
  }

  public static double fitStep(double[] data, int start, int end) {
    int count = end - start + 1;
    if (count == 1) {
      return data[start];
    }
    if (count < 0) {
      return 0;
    }
    double[] sseArray = new double[count];
    for (int i = 0; i < sseArray.length; i++) {
      sseArray[i] = 0.0;
    }
    double sum = sum(data, start, end);
    double mean = mean(data, start, end);
    double sstot = mse(data, start, end);
    double sum1 = 0.0;
    int count1 = 0;
    double m1 = 0.0;
    double sum2 = sum;
    int count2 = count;
    double m2 = mean;
    double sum1sq = 0.0;
    double sum2sq = sstot;
    double sse = sum1sq + sum2sq;
    int label = 0;

    for (int i = 0; i < sseArray.length; i++) {
      double entry = data[i + start];
      count1 ++;
      count2 --;
      if (count2 == 0) {
        sseArray[i] = sstot;
        continue;
      }
      double tmp = (mean - (entry + sum1)/count1);
      sum1sq = sum1sq + (entry-mean) * (entry-mean) - tmp * tmp * count1
        + (count1 - 1) * (mean - m1) * (mean - m1);
      tmp = (mean - (sum2 - entry)/count2);
      sum2sq = sum2sq - (entry-mean) * (entry-mean) - tmp * tmp * count2
        + (count2 + 1) * (mean - m2) * (mean - m2);
      sum1 += entry;
      sum2 -= entry;
      m1 = sum1/count1;
      m2 = sum2/count2;
      sse = sum1sq + sum2sq;
      sseArray[i] = sse;
    }

    double bestSse = Double.MAX_VALUE;
    int bestIndex = 0;
    for (int i = 0; i < count; i++) {
      if (sseArray[i] < bestSse) {
        bestSse = sseArray[i];
        bestIndex = i + start;
      }
    }
    m1 = mean(data, start, bestIndex);
    m2 = mean(data, bestIndex + 1, end);
    if (m1 < m2) {
      label = 1;
    }
    double thr = (m1 + m2)/2;
    return thr;
  }

  public void getExprData(String[] arr, double[] data) {
    for (int i = 0, il = data.length; i < il; i++) {
      data[i] = Double.NaN;
    }
    int i = start;
    for (int il = arr.length; i <= end && i < il && i < data.length; i++) {
      try {
        double v = Double.parseDouble(arr[i]);
        data[i] = v;
      }
      catch (Exception e) {
      }
    }
  }

  public void getExprData(String[] arr, double[] data, BitSet bs) {
    if (bs == null) {
      getExprData(arr, data);
      return;
    }
    for (int i = 0, il = data.length; i < il; i++) {
      data[i] = Double.NaN;
    }
    //To iterate over the true bits in a BitSet
    for (int i = bs.nextSetBit(0); i >= 0; i = bs.nextSetBit(i+1)) {
      int index = i + start;
      if ( index < arr.length && index < data.length ) {
        try {
          double v = Double.parseDouble(arr[index]);
          data[index] = v;
        }
        catch (Exception e) {
        }
      }
    }
  }

  public static double getCorrelation(double[] v1, double[] v2, int[] counts) {
    double sum_xy = 0, sum_x = 0, sum_y = 0, sum_sqx = 0, sum_sqy = 0;
    int count = 0;
    counts[0] = count;
    double res =0;
    if (v1 == null || v2 == null) {
      return res;
    }
    int length = v1.length;
    if (length > v2.length) {
      length = v2.length;
    }
    for (int i =0; i <length; i++) {
      double x = v1[i];
      double y = v2[i];
      if (!Double.isNaN(x) && !Double.isNaN(y)) {
        count ++;
        sum_xy += x * y;
        sum_x += x;
        sum_y += y;
        sum_sqx += x * x;
        sum_sqy += y * y;
      }
    }
    if (count != 0) {
      res = (sum_xy - 1.0/count * sum_x * sum_y)/
        Math.sqrt(sum_sqx - 1.0/count * sum_x * sum_x)/
        Math.sqrt(sum_sqy - 1.0/count * sum_y * sum_y);
    }
    if (Double.isNaN(res)) {
      res = 0.0;
    }
    counts[0] = count;
    return res;
  }

/*
  private static HashMap sortByValues(HashMap map) { 
    List list = new LinkedList(map.entrySet());
    // Defined Custom Comparator here
    Collections.sort(list, new Comparator() {
        public int compare(Object o1, Object o2) {
        return ((Comparable) ((Map.Entry) (o1)).getValue())
        .compareTo(((Map.Entry) (o2)).getValue());
        }
        });

    // Here I am copying the sorted list in HashMap
    // using LinkedHashMap to preserve the insertion order
    HashMap sortedHashMap = new LinkedHashMap();
    for (Iterator it = list.iterator(); it.hasNext();) {
      Map.Entry entry = (Map.Entry) it.next();
      sortedHashMap.put(entry.getKey(), entry.getValue());
    } 
    return sortedHashMap;
  }

  // Following sort by value functions have a bug: when values are equal
  // it collapses them
  public static <K, V extends Comparable<? super V>> Map<K, V> sortByValuesUp(
      Map<K, V> tempMap) {
    TreeMap<K, V> map = new TreeMap<>(buildComparatorUp(tempMap));
    map.putAll(tempMap);
    return map;
  }

  public static <K, V extends Comparable<? super V>> Map<K, V> sortByValuesDown(
      Map<K, V> tempMap) {
    TreeMap<K, V> map = new TreeMap<>(buildComparatorDown(tempMap));
    map.putAll(tempMap);
    return map;
  }

  public static <K, V extends Comparable<? super V>> Comparator<? super K>
    buildComparatorDown(final Map<K, V> tempMap) {
      return (o2, o1) -> tempMap.get(o1).compareTo(tempMap.get(o2));
    }

  public static <K, V extends Comparable<? super V>> Comparator<? super K>
    buildComparatorUp(final Map<K, V> tempMap) {
      return (o1, o2) -> tempMap.get(o1).compareTo(tempMap.get(o2));
    }
*/

  public static <K, V extends Comparable<? super V>> Map<K, V> sortByValuesUp(Map<K
, V> map) {
    return map.entrySet()
      .stream()
      .sorted(Map.Entry.comparingByValue(/*Collections.reverseOrder()*/))
      .collect(Collectors.toMap(
            Map.Entry::getKey,
            Map.Entry::getValue,
            (e1, e2) -> e1,
            LinkedHashMap::new
            ));
  }
  public static <K, V extends Comparable<? super V>> Map<K, V> sortByValuesDown(Map<K
, V> map) {
    return map.entrySet()
      .stream()
      .sorted(Map.Entry.comparingByValue(Collections.reverseOrder()))
      .collect(Collectors.toMap(
            Map.Entry::getKey,
            Map.Entry::getValue,
            (e1, e2) -> e1,
            LinkedHashMap::new
            ));
  }

  public void printCorrelation(String id) {
    printCorrelation(id, null);
  }

  public void printCorrelation(String id, String listFile) {
    String exprFile = getExprFile();
    if (exprFile == null) {
      return;
    }
    String line;
    try {
      BitSet groups = getGroups(listFile);
      String line1 = getLine(exprFile, id);
      if (line1 == null) {
        return;
      }
      String[] result1 = line1.split("\\t", -2); // -2 : Don't discard trailing nulls
      double[] data1 = new double[result1.length];
      getExprData(result1, data1, groups); 
      
      // FileReader reads text files in the default encoding.
      FileReader fileReader = 
        new FileReader(exprFile);

      // Always wrap FileReader in BufferedReader.
      BufferedReader bufferedReader = 
        new BufferedReader(fileReader);

      double[] data2 = new double[result1.length];
      HashMap<String, Double> hmap1 = new HashMap<String, Double>();
      HashMap<String, String> hmap2 = new HashMap<String, String>();
      HashMap<String, Integer> hmap3 = new HashMap<String, Integer>();
      while((line = bufferedReader.readLine()) != null) {
        String[] result = line.split("\\t", -2); // -2 : Don't discard trailing nulls
        getExprData(result, data2, groups); 
        int[] counts = new int[] {0, 0};
        double res = getCorrelation(data1, data2, counts);
        hmap1.put(result[0], res);
        hmap2.put(result[0], result[1]);
        hmap3.put(result[0], counts[0]);
      }
      // Always close files.
      bufferedReader.close();         
      Map<String, Double> map = sortByValuesDown(hmap1); 
      Set set2 = map.entrySet();
      Iterator iterator2 = set2.iterator();
      while(iterator2.hasNext()) {
        Map.Entry me2 = (Map.Entry)iterator2.next();
        int count = hmap3.get(me2.getKey());
        out.println(me2.getValue() + "\t" + count + "\t" + me2.getKey() + "\t" +
            hmap2.get(me2.getKey())); 
      }
    }
    catch(FileNotFoundException ex) {
      out.println( "Unable to open file '" + exprFile + "'");
    }
    catch(Exception ex) {
      ex.printStackTrace();
    }
  }

  public void printCorrelation2(String id1, String id2) {
    printCorrelation2(id1, id2, (String) null);
  }

  public void printCorrelation2(String id1, String id2, String listFile) {
    BitSet groups = getGroups(listFile);
    printCorrelation2(id1, id2, groups);
  }

  public void printCorrelation2(String id1, String id2, BitSet groups) {
    String exprFile = getExprFile();
    if (exprFile == null) {
      return;
    }
    String line;
    try {
      String[] lines = get2Lines(exprFile, id1, id2);
      if (lines[0] == null && lines[1] == null) {
        return;
      }
      double[] data0 = null;
      double[] data1 = null;

      int num = 0;
      if (lines[0] != null) {
        String[] result = lines[0].split("\\t", -2); // -2 : Don't discard trailing nulls
        data0 = new double[result.length];
        num = result.length;
        getExprData(result, data0, groups); 
      }
      if (lines[1] != null) {
        String[] result = lines[1].split("\\t", -2); // -2 : Don't discard trailing nulls
        data1 = new double[result.length];
        if (result.length > num) {
          num = result.length;
        }
        getExprData(result, data1, groups); 
      }
      if (num == 0) {
        return;
      } 
      // FileReader reads text files in the default encoding.
      FileReader fileReader = 
        new FileReader(exprFile);

      // Always wrap FileReader in BufferedReader.
      BufferedReader bufferedReader = 
        new BufferedReader(fileReader);

      double[] data2 = new double[num];
      HashMap<String, Double> hmap0 = new HashMap<String, Double>();
      HashMap<String, Double> hmap1 = new HashMap<String, Double>();
      HashMap<String, String> hmap2 = new HashMap<String, String>();
      HashMap<String, int[]> hmap3 = new HashMap<String, int[]>();
      while((line = bufferedReader.readLine()) != null) {
        String[] result = line.split("\\t", -2); // -2 : Don't discard trailing nulls
        getExprData(result, data2, groups); 
        int[] counts = new int[] {0, 0};
        int[] tcounts = new int[] {0, 0};
        double res = getCorrelation(data0, data2, tcounts);
        counts[0] = tcounts[0];
        hmap0.put(result[0], res);
        res = getCorrelation(data1, data2, tcounts);
        counts[1] = tcounts[0];
        hmap1.put(result[0], res);
        hmap2.put(result[0], result[1]);
        hmap3.put(result[0], counts);
      }
      // Always close files.
      bufferedReader.close();         
      Map<String, Double> map = sortByValuesDown(hmap0); 
      Set set2 = map.entrySet();
      Iterator iterator2 = set2.iterator();
      while(iterator2.hasNext()) {
        Map.Entry me2 = (Map.Entry)iterator2.next();
        int[] counts = hmap3.get(me2.getKey());
        out.println(me2.getValue() + "\t" + hmap1.get(me2.getKey())
            + "\t" + counts[0] + "\t" + counts[1]
            + "\t" + me2.getKey() + "\t" + hmap2.get(me2.getKey())); 
      }
    }
    catch(FileNotFoundException ex) {
      out.println( "Unable to open file '" + exprFile + "'");
    }
    catch(Exception ex) {
      ex.printStackTrace();
    }
  }

  public void printCorrelation3(String id1, String id2) {
    String exprFile = getExprFile();
    if (exprFile == null) {
      return;
    }
    String line;
    try {
      String[] lines = get2Lines(exprFile, id1, id2);
      if (lines[0] == null && lines[1] == null) {
        return;
      }
      double[] data0 = null;
      double[] data1 = null;

      int num = 0;
      if (lines[0] != null) {
        String[] result = lines[0].split("\\t", -2); // -2 : Don't discard trailing nulls
        data0 = new double[result.length];
        num = result.length;
        getExprData(result, data0, null); 
      }
      if (lines[1] != null) {
        String[] result = lines[1].split("\\t", -2); // -2 : Don't discard trailing nulls
        data1 = new double[result.length];
        if (result.length > num) {
          num = result.length;
        }
        getExprData(result, data1, null); 
      }
      if (num == 0) {
        return;
      } 
      ArrayList<Double> dlist = new ArrayList<Double>();
      for (int i =start; i <= end; i++) {
        if (!Double.isNaN(data0[i])) {
          dlist.add(new Double(data0[i]));
        }
      }
      double[] ddata = new double[dlist.size()];
      for (int x=0; x<dlist.size(); x++) {
        ddata[x] = dlist.get(x);
      }
      Arrays.sort(ddata);
      double thr0 = fitStep(ddata, 0, ddata.length-1);
      double v_min = ddata[0];
      double v_thr = (thr0 + v_min) / 2;
      dlist = new ArrayList<Double>();
      for (int i =start; i <= end; i++) {
        if (!Double.isNaN(data1[i])) {
          dlist.add(new Double(data1[i]));
        }
      }
      ddata = new double[dlist.size()];
      for (int x=0; x<dlist.size(); x++) {
        ddata[x] = dlist.get(x);
      }
      Arrays.sort(ddata);
      double thr1 = fitStep(ddata, 0, ddata.length-1);
      double h_min = ddata[0];
      double h_thr = (thr1 + h_min) / 2;
      int v_size = 0;
      for (int i =start; i <= end; i++) {
        if (!Double.isNaN(data0[i]) && !Double.isNaN(data1[i])) {
          if (data0[i] > v_thr && data1[i] > h_thr) {
            v_size++;
          }
        }
      }
      double[] vdata0 = new double[v_size];
      double[] vdata1 = new double[v_size];
      int index = 0;
      int count1 = 0;
      int count2 = 0;
      int count3 = 0;
      int count4 = 0;
      for (int i =start; i <= end; i++) {
        if (!Double.isNaN(data0[i]) && !Double.isNaN(data1[i])) {
          if (data0[i] > v_thr && data1[i] > h_thr) {
            vdata0[index] = data0[i];
            vdata1[index] = data1[i];
            index++;
          }
          if (data0[i] <= v_thr && data1[i] > thr1) {
            count1++;
          }
          if (data0[i] <= v_thr) {
            count2++;
          }
          if (data1[i] <= h_thr && data0[i] > thr0) {
            count3++;
          }
          if (data1[i] <= h_thr) {
            count4++;
          }
        }
      }
      int[] counts = new int[] {0, 0};
      double res1 = getCorrelation(vdata0, vdata1, counts);
      double res2 = getCorrelation(data0, data1, counts);
      out.println(id1 + "\t" + id2 + "\t" + res2 + "\t" + res1 + "\t" +
          thr0 + "\t" + thr1 + "\t" + v_min + "\t" + v_thr + "\t" + 
          h_min + "\t" + h_thr + "\t" + 
          count1 + "\t" + count2 + "\t" + (count1 /(count2 + 1.0)) + "\t" +
          count3 + "\t" + count4 + "\t" + (count3 /(count4 + 1.0)));
    }
    catch(FileNotFoundException ex) {
      out.println( "Unable to open file '" + exprFile + "'");
    }
    catch(Exception ex) {
      ex.printStackTrace();
    }
  }

  public void printCorrelation3(String id) {
    String exprFile = getExprFile();
    if (exprFile == null) {
      return;
    }
    String line;
    try {
      String line1 = getLine(exprFile, id);
      if (line1 == null) {
        return;
      }
      String[] result1 = line1.split("\\t", -2); // -2 : Don't discard trailing nulls
      double[] data1 = new double[result1.length];
      getExprData(result1, data1, null); 
      
      ArrayList<Double> dlist = new ArrayList<Double>();
      for (int i =start; i <= end; i++) {
        if (!Double.isNaN(data1[i])) {
          dlist.add(new Double(data1[i]));
        }
      }
      double[] ddata = new double[dlist.size()];
      for (int x=0; x<dlist.size(); x++) {
        ddata[x] = dlist.get(x);
      }
      Arrays.sort(ddata);
      double thr1 = fitStep(ddata, 0, ddata.length-1);
      double v_min = ddata[0];
      double v_thr = (thr1 + v_min) / 2;
      // FileReader reads text files in the default encoding.
      FileReader fileReader = 
        new FileReader(exprFile);

      // Always wrap FileReader in BufferedReader.
      BufferedReader bufferedReader = 
        new BufferedReader(fileReader);

      double[] data2 = new double[result1.length];
      while((line = bufferedReader.readLine()) != null) {
        String[] result = line.split("\\t", -2); // -2 : Don't discard trailing nulls
        getExprData(result, data2, null); 
        dlist = new ArrayList<Double>();
        for (int i =start; i <= end; i++) {
          if (!Double.isNaN(data2[i])) {
            dlist.add(new Double(data2[i]));
          }
        }
        if (dlist.size() <= 0) {
          continue;
        }
        ddata = new double[dlist.size()];
        for (int x=0; x<dlist.size(); x++) {
          ddata[x] = dlist.get(x);
        }
        Arrays.sort(ddata);
        double thr2 = fitStep(ddata, 0, ddata.length-1);
        double h_min = ddata[0];
        double h_thr = (thr2 + h_min) / 2;
        int v_size = 0;
        for (int i =start; i <= end; i++) {
          if (!Double.isNaN(data1[i]) && !Double.isNaN(data2[i])) {
            if (data1[i] > v_thr && data2[i] > h_thr) {
              v_size++;
            }
          }
        }
        double[] vdata1 = new double[v_size];
        double[] vdata2 = new double[v_size];
        int index = 0;
        int count1 = 0;
        int count2 = 0;
        int count3 = 0;
        int count4 = 0;
        for (int i =start; i <= end; i++) {
          if (!Double.isNaN(data1[i]) && !Double.isNaN(data2[i])) {
            if (data1[i] > v_thr && data2[i] > h_thr) {
              vdata1[index] = data1[i];
              vdata2[index] = data2[i];
              index++;
            }
            if (data1[i] <= v_thr && data2[i] > thr2) {
              count1++;
            }
            if (data1[i] <= v_thr) {
              count2++;
            }
            if (data2[i] <= h_thr && data1[i] > thr1) {
              count3++;
            }
            if (data2[i] <= h_thr) {
              count4++;
            }
          }
        }
        int[] counts = new int[] {0, 0};
        double res1 = getCorrelation(vdata1, vdata2, counts);
        double res2 = getCorrelation(data1, data2, counts);
        String id2 = result[0];
        out.println(id + "\t" + id2 + "\t" + res2 + "\t" + res1 + "\t" +
            thr1 + "\t" + thr2 + "\t" + v_min + "\t" + v_thr + "\t" + 
            h_min + "\t" + h_thr + "\t" + 
            count1 + "\t" + count2 + "\t" + (count1 /(count2 + 1.0)) + "\t" +
            count3 + "\t" + count4 + "\t" + (count3 /(count4 + 1.0)));
      }
      // Always close files.
      bufferedReader.close();         
    }
    catch(FileNotFoundException ex) {
      out.println( "Unable to open file '" + exprFile + "'");
    }
    catch(Exception ex) {
      ex.printStackTrace();
    }
  }

  public static double ttest(double[] data1, double[] data2, int[] counts) {
    int c_1 = 0, c_2 = 0;
    double sum_1 = 0, sum_2 = 0;
    double sum_sq1 = 0, sum_sq2 = 0;
    for (int i =0; i < data1.length; i++) {
      double x = data1[i];
      if (!Double.isNaN(x)) {
        c_1 ++;
        sum_1 += x;
        sum_sq1 += x * x;
      }
    }
    for (int i =0; i < data2.length; i++) {
      double x = data2[i];
      if (!Double.isNaN(x)) {
        c_2 ++;
        sum_2 += x;
        sum_sq2 += x * x;
      }
    }
    counts[0] = c_1;
    counts[1] = c_2;
    if (c_1 <= 0 || c_2 <= 0) {
      return 1;
    }
    double m_1 = sum_1/c_1;
    double m_2 = sum_2/c_2;
    double v_1 = (sum_sq1/c_1 - m_1 * m_1);
    if (c_1 > 1) {
         v_1 = v_1 * (c_1 * 1.0/ (c_1 - 1));
    }
    double v_2 = (sum_sq2/c_2 - m_2 * m_2);
    if (c_2 > 1) {
         v_2 = v_2 * (c_2 * 1.0/ (c_2 - 1));
    }
    double v = Math.abs(v_1/c_1 + v_2/c_2);
    if (v == 0) {
      v = 1;
    }
    double sd = Math.sqrt(v);
    double t = Math.abs((m_1 - m_2) / sd);
    if (t <= 0) {
      return 1;
    }
    double df = 1;
    double dn1 = (v_1/c_1) * (v_1/c_1);
    if (c_1 > 1) {
        dn1 = dn1 / (c_1 - 1);
    }
    double dn2 = (v_2/c_2) * (v_2/c_2);
    if (c_2 > 1) {
        dn2 = dn2 / (c_2 - 1);
    }
    double dn = dn1 + dn2;
    if (dn == 0) { dn = 1; }
    df = v * v / dn;
    if (df <= 0) { df = 1; }
    specialFunctions sf = new specialFunctions();
    double p = 2 * sf.stDist(df, t);
    return p;
  }

  public void printDiff(String listFile) {
    String exprFile = getExprFile();
    if (exprFile == null) {
      return;
    }
    String line;
    try {
      HashMap<String, Integer> hmap = new HashMap<String, Integer>();
      for (int i = start; i <= end; i++) {
        hmap.put(headers[i], i);
      }

      FileReader fileReader = new FileReader(listFile);
      BufferedReader bufferedReader = new BufferedReader(fileReader);
      ArrayList<Integer> idlist1 = new ArrayList<Integer>();
      ArrayList<Integer> idlist2 = new ArrayList<Integer>();
      String group1 = null;
      String group2 = null;
      while((line = bufferedReader.readLine()) != null) {
        String[] result = line.split("\\t", -2);
        if (result.length < 2) {
          continue;
        }
        if (group1 == null || Objects.equals(group1, result[1])) {
          group1 = result[1];
        }
        else if(group2 == null || Objects.equals(group2, result[1])) {
          group2 = result[1];
        }
        if (Objects.equals(group1, result[1]) && hmap.containsKey(result[0])) {
          idlist1.add(hmap.get(result[0]));
        }
        if (Objects.equals(group2, result[1]) && hmap.containsKey(result[0])) {
          idlist2.add(hmap.get(result[0]));
        }
      }
      if (idlist1.size() <= 0 || idlist2.size() <= 0) {
        return;
      }
      // Always close files.
      bufferedReader.close();         
      fileReader = new FileReader(exprFile);
      bufferedReader = new BufferedReader(fileReader);

      double[] data = new double[headers.length];
      double[] data1 = new double[idlist1.size()];
      double[] data2 = new double[idlist2.size()];
      line = bufferedReader.readLine();
      HashMap<String, Double> hmap0 = new HashMap<String, Double>();
      HashMap<String, Double> hmap1 = new HashMap<String, Double>();
      HashMap<String, String> hmap2 = new HashMap<String, String>();
      HashMap<String, int[]> hmap3 = new HashMap<String, int[]>();
      int lineno = 0;
      while((line = bufferedReader.readLine()) != null) {
        String[] result = line.split("\\t", -2); // -2 : Don't discard trailing nulls
        getExprData(result, data, null); 
        for (int i =0; i < idlist1.size(); i++) {
          data1[i] = data[idlist1.get(i)];
        }
        for (int i =0; i < idlist2.size(); i++) {
          data2[i] = data[idlist2.get(i)];
        }
        int[] counts = new int[] {0, 0};
        double p = ttest(data1, data2, counts);
        double m1 = mean(data1, 0, data1.length - 1);
        double m2 = mean(data2, 0, data2.length - 1);
        hmap0.put(result[0], (m2 - m1));
        hmap1.put(result[0], p);
        hmap2.put(result[0], result[1]);
        hmap3.put(result[0], counts);
        lineno++;
      }
      // Always close files.
      bufferedReader.close();         
      Map<String, Double> map = sortByValuesDown(hmap0); 
      Set set2 = map.entrySet();
      Iterator iterator2 = set2.iterator();
      int index = 0;
      while(iterator2.hasNext()) {
        Map.Entry me2 = (Map.Entry)iterator2.next();
        int[] counts = hmap3.get(me2.getKey());
        out.println(me2.getValue() + "\t" + hmap1.get(me2.getKey())
            + "\t" + counts[0] + "\t" + counts[1]
            + "\t" + me2.getKey() + "\t" + hmap2.get(me2.getKey())); 
        index++;
      }
    }
    catch(FileNotFoundException ex) {
      out.println( "Unable to open file '" + exprFile + "'");
    }
    catch(Exception ex) {
      ex.printStackTrace();
    }
  }

  public void readAFile(String aFile, ArrayList<String> groups,
        ArrayList< ArrayList<Integer> > groupIDs) {
    try {
      HashMap<String, Integer> hmap = new HashMap<String, Integer>();
      for (int i = start; i <= end; i++) {
        hmap.put(headers[i], i);
      }
      FileReader fileReader = new FileReader(aFile);
      BufferedReader bufferedReader = new BufferedReader(fileReader);
      HashMap<String, Integer> gs = new HashMap<String, Integer>();
      int index = 0;
      String line;
      while((line = bufferedReader.readLine()) != null) {
        String[] result = line.split("\\t", -2);
        String group = "0";
        if (result.length < 1) {
          continue;
        }
        if (result.length > 1) {
          group = result[1];
        }
        if (!hmap.containsKey(result[0])) {
          continue;
        }
        String nm = "";
        if (result.length > 2) {
          nm = result[2];
        }
        if (!gs.containsKey(group)) {
          gs.put(group, index);
          groups.add(nm);
          groupIDs.add(new ArrayList<Integer>());
          groupIDs.get(index).add(hmap.get(result[0]));
          index++;
        }
        else {
          int i = gs.get(group);
          groupIDs.get(i).add(hmap.get(result[0]));
        }
      }
      // Always close files.
      bufferedReader.close();         
    }
    catch(Exception ex) {
      ex.printStackTrace();
    }
  }

  public void readGFile(String gFile, ArrayList<String> groups,
      ArrayList< ArrayList<String> > groupIDs,
      HashMap<String, Long> ids) {
    try {
      FileReader fileReader = new FileReader(gFile);
      BufferedReader bufferedReader = new BufferedReader(fileReader);
      HashMap<String, Integer> gs = new HashMap<String, Integer>();
      int index = 0;
      String line;
      while((line = bufferedReader.readLine()) != null) {
        String[] result = line.split("\\t", -2);
        String group = "0";
        if (result.length < 1) {
          continue;
        }
        if (!ids.containsKey(result[0])) {
          continue;
        }
        if (result.length > 1) {
          group = result[1];
        }
        String nm = "";
        if (result.length > 2) {
          nm = result[2];
        }
        if (!gs.containsKey(group)) {
          gs.put(group, index);
          groups.add(nm);
          groupIDs.add(new ArrayList<String>());
          groupIDs.get(index).add(result[0]);
          index++;
        }
        else {
          int i = gs.get(group);
          groupIDs.get(i).add(result[0]);
        }
      }
      // Always close files.
      bufferedReader.close();         
    }
    catch(Exception ex) {
      ex.printStackTrace();
    }
  }

  public HashMap<String, Long> getIDs() {
    try {
      String exprFile = getExprFile();
      HashMap<String, Long> res = new HashMap<String, Long>();
      FileR reader = new FileR(exprFile);
      String line = reader.readLine();
      long pos = reader.filePtr();
      while((line = reader.readLine()) != null) {
        String[] result = line.split("\\t", -2);
        if (result.length < 1) {
          continue;
        }
        res.put(result[0], pos);
        System.out.println(result[0] + " " + pos);
        pos = reader.filePtr();
      }
      reader.close();
      return res;
    }
    catch(Exception ex) {
      ex.printStackTrace();
    }
    return null;
  }

  public static <V> int getLSize(ArrayList< ArrayList<V> > list) {
    int count = 0;
    for (int i=0; i < list.size(); i++) {
      count += list.get(i).size();
    }
    return count;
  }

  public static double getDouble(String s) {
    try {
      double v = Double.parseDouble(s);
      return v;
    }
    catch (Exception e) {
    }
    return Double.NaN;
  }

  public void generateHeatmap (String gFile, String aFile, String sFile) {
    try {
      ArrayList<String> geneGroups = new ArrayList<String>();
      ArrayList<String> arrayGroups = new ArrayList<String>();
      ArrayList< ArrayList<String> > geneIDs = new ArrayList< ArrayList<String> >();
      ArrayList< ArrayList<Integer> > arrayIDs = new ArrayList< ArrayList<Integer> >();
      HashMap<String, Long> ids = getIDs();
      readGFile(gFile, geneGroups, geneIDs, ids);
      readAFile(aFile, arrayGroups, arrayIDs);
      Heatmap map = new Heatmap(sFile);
      int anum = getLSize(arrayIDs);
      int gnum = getLSize(geneIDs);
      map.setSize(anum, gnum);
      map.init();
      String exprFile = getExprFile();
      FileR reader = new FileR(exprFile);
      int y = 0;
      for (int i=0; i < geneIDs.size(); i++) {
        for (int gi = 0; gi < geneIDs.get(i).size(); gi++) {
          String id = geneIDs.get(i).get(gi);
          long ptr = ids.get(id);
          long currPtr = reader.filePtr();
          if (ptr != currPtr) {
            reader.seek(ptr);
          }
          String line = reader.readLine();
          String[] result = line.split("\\t", -2);
          System.out.println(id + " " + result[0] + " " + result[1]);
          int x = 0;
          for (int j=0; j < arrayIDs.size(); j++) {
            for (int aj = 0; aj < arrayIDs.get(i).size() ; aj++) {
              int vj = arrayIDs.get(i).get(aj);
              double val = Double.NaN;
              if (vj < result.length) {
                val = getDouble(result[vj]);
              }
              map.plotCell(x, y, val);
              x++;
            }
          }
          y++;
        }
      }
      map.close();

    }
    catch(Exception ex) {
      ex.printStackTrace();
    }
  }

  public void printStats(String id) {
    printStats(id, null);
  }

  public void printStats(String id, String listFile) {
    String exprFile = getExprFile();
    if (exprFile == null) {
      return;
    }
    String line;
    try {
      BitSet groups = getGroups(listFile);
      String line1 = getLine(exprFile, id);
      if (line1 == null) {
        return;
      }
      String[] result1 = line1.split("\\t", -2); // -2 : Don't discard trailing nulls
      double[] data1 = new double[result1.length];
      getExprData(result1, data1, groups); 
      
      // FileReader reads text files in the default encoding.
      FileReader fileReader = 
        new FileReader(exprFile);

      // Always wrap FileReader in BufferedReader.
      BufferedReader bufferedReader = 
        new BufferedReader(fileReader);

      double[] data2 = new double[result1.length];
      HashMap<String, Correlation> hmap1 = new HashMap<String, Correlation>();
      HashMap<String, String> hmap2 = new HashMap<String, String>();
      HashMap<String, LinearRegression> hmap3 = new HashMap<String, LinearRegression>();
      while((line = bufferedReader.readLine()) != null) {
        String[] result = line.split("\\t", -2); // -2 : Don't discard trailing nulls
        getExprData(result, data2, groups); 
        hmap1.put(result[0], new Correlation(data1, data2));
        hmap2.put(result[0], result[1]);
        hmap3.put(result[0], new LinearRegression(data1, data2));
      }
      // Always close files.
      bufferedReader.close();         
      Map<String, Correlation> map = sortByValuesDown(hmap1); 
      Set set2 = map.entrySet();
      Iterator iterator2 = set2.iterator();
      while(iterator2.hasNext()) {
        Map.Entry me2 = (Map.Entry)iterator2.next();
        out.println(me2.getValue() + "\t" + hmap3.get(me2.getKey()) + "\t" +
            me2.getKey() + "\t" +
            hmap2.get(me2.getKey())); 
      }
    }
    catch(FileNotFoundException ex) {
      out.println( "Unable to open file '" + exprFile + "'");
    }
    catch(Exception ex) {
      ex.printStackTrace();
    }
  }

  public void topGenes(String numStr) {
    try {
      if (!hasInfo()) {
        return;
      }
      String infoFile = getInfo();
      String line;
      int num = Integer.parseInt(numStr);
      ArrayList<String> idlist = new ArrayList<String>();
      ArrayList<String> namelist = new ArrayList<String>();
      ArrayList<Double> drlist = new ArrayList<Double>();
      ArrayList<Double> sdlist = new ArrayList<Double>();
      FileReader fileReader = new FileReader(infoFile);
      BufferedReader bufferedReader = new BufferedReader(fileReader);
      line = bufferedReader.readLine();
      while((line = bufferedReader.readLine()) != null) {
        String[] result = line.split("\\t", -2);
        if (result.length < 9) {
          continue;
        }
        double dr = Double.parseDouble(result[7]) - Double.parseDouble(result[6]);
        double sd = Double.parseDouble(result[8]);
        idlist.add(result[0]);
        namelist.add(result[1]);
        drlist.add(new Double(dr));
        sdlist.add(new Double(sd));
      }
      bufferedReader.close(); 
      double[] drdata = new double[drlist.size()];
      double[] sddata = new double[sdlist.size()];
      for (int x=0; x<idlist.size(); x++) {
        drdata[x] = drlist.get(x);
        sddata[x] = sdlist.get(x);
      }
      Arrays.sort(drdata);
      Arrays.sort(sddata);
      double drthr = fitStep(drdata, 0, drdata.length-1);
      double sdthr = fitStep(sddata, 0, sddata.length-1);
      //out.println(drthr);
      //out.println(sdthr);
      //double[] data = {1, 1, 1, 2, 3, 1, 4, 5, 4, 6, 4, 5};
      //double thr = fitStep(data, 0, data.length-1);
      //out.println(thr);
      HashMap<Integer, Double> res = new HashMap<Integer, Double>();
      for (int x=0; x<idlist.size(); x++) {
        if (drlist.get(x) > drthr && sdlist.get(x) > sdthr) {
          res.put(x, drlist.get(x));
        }
      }
      Map<Integer, Double> map = sortByValuesDown(res); 
      Set set2 = map.entrySet();
      Iterator iterator2 = set2.iterator();
      int index = 0;
      while(iterator2.hasNext() && index < num) {
        Map.Entry me2 = (Map.Entry)iterator2.next();
        Integer xi = (Integer) me2.getKey();
        int x = xi.intValue();
        out.println(String.format("%1$.2f", me2.getValue()) + "\t" + 
            idlist.get(x) + "\t" + namelist.get(x));
        index++;
      }
    }
    catch(Exception ex) {
      ex.printStackTrace();
    }
  }

  public void getInfoJSON(String listFile) {
    try {
      if (!hasInfo()) {
        return;
      }
      String infoFile = getInfo();
      String line;
      ArrayList<String> idlist = new ArrayList<String>();
      HashMap<String, Integer> listmap = new HashMap<String, Integer>();
      FileReader fileReader = new FileReader(listFile);
      BufferedReader bufferedReader = new BufferedReader(fileReader);
      while((line = bufferedReader.readLine()) != null) {
        String[] result = line.split("\\t", -2);
        if (result.length < 1) {
          continue;
        }
        if (!listmap.containsKey(result[0])) {
          int i = idlist.size();
          idlist.add(result[0]);
          listmap.put(result[0], new Integer(i));
        }
      }
      bufferedReader.close(); 
      HashMap<Integer, String> infomap = new HashMap<Integer, String>();
      HashMap<Integer, Double> drmap = new HashMap<Integer, Double>();
      fileReader = new FileReader(infoFile);
      bufferedReader = new BufferedReader(fileReader);
      line = bufferedReader.readLine();
      while((line = bufferedReader.readLine()) != null) {
        String[] result = line.split("\\t", -2);
        if (result.length < 9) {
          continue;
        }
        double dr = Double.parseDouble(result[7]) - Double.parseDouble(result[6]);
        if (listmap.containsKey(result[0])) {
          Integer i = listmap.get(result[0]);
          drmap.put(i, new Double(dr));
          infomap.put(i, infoJSON(result));
        }
      }
      bufferedReader.close(); 
      Map<Integer, Double> map = sortByValuesDown(drmap); 
      Set set2 = map.entrySet();
      Iterator iterator2 = set2.iterator();
      int index = 0;
      out.print("[");
      while(iterator2.hasNext()) {
        Map.Entry me2 = (Map.Entry)iterator2.next();
        Integer xi = (Integer) me2.getKey();
        int x = xi.intValue();
        if (index > 0) {
            out.print(",\n");
        }
        out.print(infomap.get(xi));
        index++;
      }
      out.println("]");
    }
    catch(Exception ex) {
      ex.printStackTrace();
    }
  }

  public static void main(String[] args) {
    if (args.length < 1) {
      System.out.println("Usage: java Hegemon <cmd> <args> ... <args>");
      System.exit(1);
    }
    String cmd = args[0];
    if (cmd.equals("balanced") && args.length < 3) {
      System.out.println("Usage: java Hegemon balanced pre singleCutoff singleThr");
      System.exit(1);
    }
    if (cmd.equals("balanced")) {
      Hegemon h = new Hegemon(args[1]);
      h.printBalanced(args[2], args[3]);
    }
    if (cmd.equals("hidr") && args.length < 2) {
      System.out.println("Usage: java Hegemon hidr pre");
      System.exit(1);
    }
    if (cmd.equals("hidr")) {
      Hegemon h = new Hegemon(args[1]);
      h.printHiDr();
    }
    if (cmd.equals("boolean") && args.length < 3) {
      System.out.println("Usage: java Hegemon boolean pre id <listFile>");
      System.exit(1);
    }
    if (cmd.equals("boolean")) {
      Hegemon h = new Hegemon(args[1]);
      if (args.length < 4) {
        h.printBoolean(args[2]);
      }
      else {
        h.printBoolean(args[2], args[3]);
      }
    }
    if (cmd.equals("Boolean") && args.length < 3) {
      System.out.println("Usage: java Hegemon Boolean pre idfile <listFile>");
      System.exit(1);
    }
    if (cmd.equals("Boolean")) {
      Hegemon h = new Hegemon(args[1]);
      if (args.length < 4) {
        h.printBooleanFile(args[2]);
      }
      else {
        h.printBooleanFile(args[2], args[3]);
      }
    }
    if (cmd.equals("corr") && args.length < 3) {
      System.out.println("Usage: java Hegemon corr expr.txt id <listFile>");
      System.exit(1);
    }
    if (cmd.equals("corr")) {
      Hegemon h = new Hegemon(args[1]);
      if (args.length < 4) {
        h.printCorrelation(args[2]);
      }
      else {
        h.printCorrelation(args[2], args[3]);
      }
    }
    if (cmd.equals("corr2") && args.length < 4) {
      System.out.println("Usage: java Hegemon corr2 expr.txt id1 id2 <listFile>");
      System.exit(1);
    }
    if (cmd.equals("corr2")) {
      Hegemon h = new Hegemon(args[1]);
      if (args.length < 5) {
        h.printCorrelation2(args[2], args[3]);
      }
      else {
        h.printCorrelation2(args[2], args[3], args[4]);
      }
    }
    if (cmd.equals("corr3") && args.length < 3) {
      System.out.println("Usage: java Hegemon corr3 pre id1 [id2]");
      System.exit(1);
    }
    if (cmd.equals("corr3")) {
      Hegemon h = new Hegemon(args[1]);
      if (args.length < 4) {
        h.printCorrelation3(args[2]);
      }
      else {
        h.printCorrelation3(args[2], args[3]);
      }
    }
    if (cmd.equals("diff") && args.length < 3) {
      System.out.println("Usage: java Hegemon diff expr.txt listFile");
      System.exit(1);
    }
    if (cmd.equals("diff")) {
      Hegemon h = new Hegemon(args[1]);
      h.printDiff(args[2]);
    }
    if (cmd.equals("heatmap") && args.length < 4) {
      System.out.println("Usage: java Hegemon heatmap expr.txt gListFile aListFile setupFile");
      System.exit(1);
    }
    if (cmd.equals("heatmap")) {
      Hegemon h = new Hegemon(args[1]);
      h.generateHeatmap(args[2], args[3], args[4]);
    }
    if (cmd.equals("stats") && args.length < 3) {
      System.out.println("Usage: java Hegemon stats expr.txt id <listFile>");
      System.exit(1);
    }
    if (cmd.equals("stats")) {
      Hegemon h = new Hegemon(args[1]);
      if (args.length < 4) {
        h.printStats(args[2]);
      }
      else {
        h.printStats(args[2], args[3]);
      }
    }
    if (cmd.equals("topgenes") && args.length < 3) {
      System.out.println("Usage: java Hegemon topgenes pre num");
      System.exit(1);
    }
    if (cmd.equals("topgenes")) {
      Hegemon h = new Hegemon(args[1]);
      if (args.length < 4) {
        h.topGenes(args[2]);
      }
    }
    if (cmd.equals("getinfojson") && args.length < 3) {
      System.out.println("Usage: java Hegemon getinfojson pre listfile");
      System.exit(1);
    }
    if (cmd.equals("getinfojson")) {
      Hegemon h = new Hegemon(args[1]);
      if (args.length < 4) {
        h.getInfoJSON(args[2]);
      }
    }
  }
}
