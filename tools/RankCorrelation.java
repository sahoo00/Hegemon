package tools;

import java.io.*;
import java.util.*;

class RankCorrelation {
  String file;
  int start, end;

  public RankCorrelation(String f) {
    file = f;
    start = 2;
    String[] headers = getHeaders();
    end = headers.length - 1;
  }

  public String[] getHeaders() {
    try {
      String exprFile = file;
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

  public Set<String> getListIDs(String listFile, String seed)  {
    if (listFile == null) {
      if (seed != null) {
        HashSet<String> idlist = new HashSet<String>();
        idlist.add(seed);
        return idlist;
      }
      return null;
    }
    try {
      String line;
      FileReader fileReader = new FileReader(listFile);
      BufferedReader bufferedReader = new BufferedReader(fileReader);
      HashSet<String> idlist = new HashSet<String>();
      if (seed != null) {
        idlist.add(seed);
      }
      while((line = bufferedReader.readLine()) != null) {
        String[] result = line.split("\\t", -2);
        idlist.add(result[0]);
      }
      return idlist;
    }
    catch (Exception e) {
      if (seed != null) {
        HashSet<String> idlist = new HashSet<String>();
        idlist.add(seed);
        return idlist;
      }
      return null;
    }
  }

  public void printRanks() {
    printRanks(null);
  }

  public void printRanks(String listFile) {
    printRanks(listFile, null);
  }

  public void printRanks(String listFile, String seed) {
    String exprFile = file;
    if (exprFile == null) {
      return;
    }
    try {
      Set<String> keys = getListIDs(listFile, seed);
      int index1 = 0;
      String line;
      String[] result1;
      FileReader fileReader = 
        new FileReader(exprFile);

      // Always wrap FileReader in BufferedReader.
      BufferedReader bufferedReader = 
        new BufferedReader(fileReader);

      line = bufferedReader.readLine();
      if (line == null) {
        return;
      }
      result1 = line.split("\\t", -2);
      while(keys != null || seed != null) {
        if (keys != null && keys.contains(result1[0])) {
          if (seed == null) {
            break;
          }
        }
        if (seed != null && seed.equals(result1[0])) {
          break;
        }
        line = bufferedReader.readLine();
        if (line == null) {
          return;
        }
        result1 = line.split("\\t", -2);
      }
      System.err.println(result1[0] + " " + index1);

      double[] data1 = new double[result1.length];
      double[] data2 = new double[result1.length];

      getExprData(result1, data1);

      bufferedReader.close();         
      fileReader = new FileReader(exprFile);
      bufferedReader = new BufferedReader(fileReader);
      HashMap<String, Double> hdata = new HashMap<String, Double>();
      HashMap<String, Double> hmap1 = new HashMap<String, Double>();
      HashMap<String, String> hmap2 = new HashMap<String, String>();
      hmap1.put(result1[0], 2.0);
      int count = 0;
      while((line = bufferedReader.readLine()) != null) {
        String[] result = line.split("\\t", -2); // -2 : Don't discard trailing nulls
        if (keys != null && !keys.contains(result[0])) {
          continue;
        }
        if (count == (index1 + 1)) {
          result1 = result;
        }
        getExprData(result, data2);
        Correlation corr = new Correlation(data1, data2);
        LinearRegression reg = new LinearRegression(data1, data2);
        double slope = reg.slope();
        if (slope > 1) { slope = 1/slope; }
        double score = corr.coefficient() * corr.coefficient() +
            slope * slope;
        hmap1.put(result[0], score);
        hmap2.put(result[0], result[1]);
        count++;
      }
      // Always close files.
      bufferedReader.close();         

      HashMap<String, Double> hseed = hmap1;

      Map<String, Double> map = Hegemon.sortByValuesUp(hmap1); 
      Set set2 = map.entrySet();
      Iterator iterator2 = set2.iterator();
      int index = 0;
      while(iterator2.hasNext()) {
        Map.Entry me2 = (Map.Entry)iterator2.next();
        String id = (String) me2.getKey();
        double score = hseed.get(id);
        hdata.put(id, index * score/ (2 * count));
        index++;
      }

      int totalCount = count;
      for (index1 = 0 ; index1 < count; index1++) {
        System.err.println(result1[0] + " " + index1);
        getExprData(result1, data1);
        fileReader = new FileReader(exprFile);
        bufferedReader = new BufferedReader(fileReader);
        count = 0;
        hmap1 = new HashMap<String, Double>();
        while((line = bufferedReader.readLine()) != null) {
          String[] result = line.split("\\t", -2); // -2 : Don't discard trailing nulls
          if (keys != null && !keys.contains(result[0])) {
            continue;
          }
          if (count == (index1 + 1)) {
            result1 = result;
          }
          getExprData(result, data2);
          Correlation corr = new Correlation(data1, data2);
          LinearRegression reg = new LinearRegression(data1, data2);
          double slope = reg.slope();
          if (slope > 1) { slope = 1/slope; }
          double score = corr.coefficient() * corr.coefficient() +
            slope * slope;
          hmap1.put(result[0], score);
          count++;
        }
        // Always close files.
        bufferedReader.close();         
        map = Hegemon.sortByValuesUp(hmap1); 
        set2 = map.entrySet();
        iterator2 = set2.iterator();
        index = 0;
        while(iterator2.hasNext()) {
          Map.Entry me2 = (Map.Entry)iterator2.next();
          String id = (String) me2.getKey();
          double rank = hdata.get(id);
          double score = hseed.get(id);
          hdata.put(id, rank + index * score / (2 * count));
          index++;
        }

      }
      map = Hegemon.sortByValuesDown(hdata); 
      set2 = map.entrySet();
      iterator2 = set2.iterator();
      while(iterator2.hasNext()) {
        Map.Entry me2 = (Map.Entry)iterator2.next();
        System.out.println(me2.getValue() + "\t" + me2.getKey() + "\t" +
            hmap2.get(me2.getKey())); 

      }

    }
    catch(FileNotFoundException ex) {
      System.out.println( "Unable to open file '" + exprFile + "'");
    }
    catch(Exception ex) {
      ex.printStackTrace();
    }
  }

  public static void main(String[] args) {
    if (args.length < 1) {
      System.out.println("Usage: java RankCorrelation <cmd> <args> ... <args>");
      System.exit(1);
    }
    String cmd = args[0];
    if (cmd.equals("rank") && args.length < 2) {
      System.out.println("Usage: java Hegemon rank exprFile [listFile [id]]");
      System.exit(1);
    }
    if (cmd.equals("rank")) {
      RankCorrelation h = new RankCorrelation(args[1]);
      if (args.length < 3) {
        h.printRanks();
      }
      else if (args.length < 4) {
        h.printRanks(args[2]);
      }
      else {
        h.printRanks(args[2], args[3]);
      }
    }
  }
}
