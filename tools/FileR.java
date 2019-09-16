package tools;

import java.io.*;
import java.util.*;
import java.lang.reflect.Field;

public class FileR {
  
    String filename;
    RandomAccessFile raf;
    BufferedReader reader;
    long currentOffset = 0;
    long previousOffset = -1;
    long index = 0;
    
    public FileR (String f) {
      try {
        filename = f;
        raf = new RandomAccessFile(filename, "r");
        reader = new BufferedReader(new FileReader(raf.getFD()));
      } catch (Exception ex) {
        ex.printStackTrace();
      }
    }

    public void close(){
      try {
        raf.close();
      } catch (Exception ex) {
        ex.printStackTrace();
      }
    }

    public long getLineNo() {
        return index;
    }
    
    private static int getOffset(BufferedReader bufferedReader) throws Exception {
      Field field = BufferedReader.class.getDeclaredField("nextChar");
      int result = 0;
      try {
	field.setAccessible(true);
	result = (Integer) field.get(bufferedReader);
      } finally {
	field.setAccessible(false);
      }
      return result;
    }

    public synchronized long filePtr() throws Exception {
      long fileOffset = raf.getFilePointer();
      if (fileOffset != previousOffset) {
	if (previousOffset != -1) {
	  currentOffset = previousOffset;
	}
	previousOffset = fileOffset;
      }
      int bufferOffset = getOffset(reader);
      long realPosition = currentOffset + bufferOffset;
      return realPosition;
    }

    public String readLine() throws Exception {
      index++;
      String line = reader.readLine();
      return line;
    }

    public void seek(long pos) throws IOException {
      if(pos < 0) { return; }
      if(pos > raf.length()) { return; }
      raf.seek(pos);
      currentOffset = raf.getFilePointer();
      previousOffset = -1;
      reader = new BufferedReader(new FileReader(raf.getFD()));
    }

}
