import sys

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
	#start = 0		# start and end are indices in arr	
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

	return {"cutoff": bestIndex+1, "bestSse": bestSse, "sstot": sstot, "statistic" : statistic, "threshold": thr, "label":label}


def main():
	inputarr = [float(s) for s in open(sys.argv[1]).readline().split(",")]
	print("input is: " + ', '.join([str(s) for s in inputarr]))
	print("mean is: " + str(meanf(inputarr)))
	print("variance is "+ str(variancef(inputarr)))
	print("mse is "+ str(msef(inputarr)))
	print("standard deviation is " + str(stdevf(inputarr)))
	print("fit step is: " + str(fitstep(inputarr)))

if __name__ == "__main__":
	main()

