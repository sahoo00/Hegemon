/**
 * The HelloWorldApp class implements an application that
 * simply prints "Hello World!" to standard output.
 */
import java.io.*;
import java.util.*;

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
      for (int i =start; i < end; i++) {
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

  public void printBoolean(String id, String listFile) {
    if (!hasBv()) {
      return;
    }
    String bvFile = getBv();
    String line;
    try {
      Set<String> keys = getFilter();
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
        out.println(result[0] + "\t" + strJoin("\t", bnum) + "\t" +
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

  public void printBoolean(String id) {
    printBoolean(id, null);
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

  public static double getCorrelation(double[] v1, double[] v2) {
    double sum_xy = 0, sum_x = 0, sum_y = 0, sum_sqx = 0, sum_sqy = 0;
    int count = 0;
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
*/
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
      while((line = bufferedReader.readLine()) != null) {
        String[] result = line.split("\\t", -2); // -2 : Don't discard trailing nulls
        getExprData(result, data2, groups); 
        double res = getCorrelation(data1, data2);
        hmap1.put(result[0], res);
        hmap2.put(result[0], result[1]);
      }
      // Always close files.
      bufferedReader.close();         
      Map<String, Double> map = sortByValuesDown(hmap1); 
      Set set2 = map.entrySet();
      Iterator iterator2 = set2.iterator();
      while(iterator2.hasNext()) {
        Map.Entry me2 = (Map.Entry)iterator2.next();
        out.println(me2.getValue() + "\t" + me2.getKey() + "\t" +
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
    printCorrelation2(id1, id2, null);
  }

  public void printCorrelation2(String id1, String id2, String listFile) {
    String exprFile = getExprFile();
    if (exprFile == null) {
      return;
    }
    String line;
    try {
      BitSet groups = getGroups(listFile);
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
      while((line = bufferedReader.readLine()) != null) {
        String[] result = line.split("\\t", -2); // -2 : Don't discard trailing nulls
        getExprData(result, data2, groups); 
        double res = getCorrelation(data0, data2);
        hmap0.put(result[0], res);
        res = getCorrelation(data1, data2);
        hmap1.put(result[0], res);
        hmap2.put(result[0], result[1]);
      }
      // Always close files.
      bufferedReader.close();         
      Map<String, Double> map = sortByValuesDown(hmap0); 
      Set set2 = map.entrySet();
      Iterator iterator2 = set2.iterator();
      while(iterator2.hasNext()) {
        Map.Entry me2 = (Map.Entry)iterator2.next();
        out.println(me2.getValue() + "\t" + hmap1.get(me2.getKey()) 
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

  public static double ttest(double[] data1, double[] data2) {
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
      while((line = bufferedReader.readLine()) != null) {
        String[] result = line.split("\\t", -2); // -2 : Don't discard trailing nulls
        getExprData(result, data, null); 
        for (int i =0; i < idlist1.size(); i++) {
          data1[i] = data[idlist1.get(i)];
        }
        for (int i =0; i < idlist2.size(); i++) {
          data2[i] = data[idlist2.get(i)];
        }
        double p = ttest(data1, data2);
        double m1 = mean(data1, 0, data1.length - 1);
        double m2 = mean(data2, 0, data2.length - 1);
        hmap0.put(result[0], (m2 - m1));
        hmap1.put(result[0], p);
        hmap2.put(result[0], result[1]);
      }
      // Always close files.
      bufferedReader.close();         
      Map<String, Double> map = sortByValuesDown(hmap0); 
      Set set2 = map.entrySet();
      Iterator iterator2 = set2.iterator();
      while(iterator2.hasNext()) {
        Map.Entry me2 = (Map.Entry)iterator2.next();
        out.println(me2.getValue() + "\t" + hmap1.get(me2.getKey()) 
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


  public static void main(String[] args) {
    if (args.length < 1) {
      System.out.println("Usage: java Hegemon <cmd> <args> ... <args>");
      System.exit(1);
    }
    String cmd = args[0];
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
    if (cmd.equals("diff") && args.length < 3) {
      System.out.println("Usage: java Hegemon diff expr.txt listFile");
      System.exit(1);
    }
    if (cmd.equals("diff")) {
      Hegemon h = new Hegemon(args[1]);
      h.printDiff(args[2]);
    }
  }
}
