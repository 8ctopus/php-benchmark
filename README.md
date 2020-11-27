# PHP benchmark script

Benchmark your php code. The project is built on top of the original work from Alessandro Torrisi [www.php-benchmark-script.com](http://www.php-benchmark-script.com)

# examples

## does xdebug slow down code execution?

```bash
# disable xdebug extension in php.ini
$ php src/benchmark.php --iterations 1000 --time-per-iteration 50 --save xdebug_off

# enable xdebug extension
$ php src/benchmark.php --iterations 1000 --time-per-iteration 50 --save xdebug_on

# compare
$ php src/compare.php --file1 benchmark_xdebug_off_20201127-0946.txt --file2 benchmark_xdebug_on_20201127-0939.txt
------------------------------------------------
test_math
mean               :      3762      531   -85.9%
median             :      4226      568   -86.6%
mode               :      4655      596   -87.2%
minmum             :       918      188   -79.5%
maximum            :      4722      612   -87.0%
quartile 1         :      3081      490   -84.1%
quartile 3         :      4580      595   -87.0%
IQ range           :      1498      105   -93.0%
std deviation      :       984       87   -91.1%
normality          :     11.0%    11.0%
------------------------------------------------
```

# definitions

> The mean is the same as the average value of a data set and is found using a calculation. Add up all of the numbers and divide by the number of numbers in the data set.

> The median is the central number of a data set. Arrange data points from smallest to largest and locate the central number. This is the median. If there are 2 numbers in the middle, the median is the average of those 2 numbers.

> The mode is the number in a data set that occurs most frequently. Count how many times each number occurs in the data set. The mode is the number with the highest tally. It's ok if there is more than one mode. And if all numbers occur the same number of times there is no mode.

> -- <cite>[calculatorsoup.com](https://www.calculatorsoup.com/calculators/statistics/mean-median-mode.php)</cite>

> The interquartile range (IQR) is the difference between the first quartile and third quartile. The formula for this is:

> IQR = Q3 - Q1

> There are many measurements of the variability of a set of data. Both the range and standard deviation tell us how spread out our data is. The problem with these descriptive statistics is that they are quite sensitive to outliers. A measurement of the spread of a dataset that is more resistant to the presence of outliers is the interquartile range.

> -- <cite>[thoughtco.com](https://www.thoughtco.com/what-is-the-interquartile-range-3126245)</cite>

> Outliers are values that lie above the Upper Fence or below the Lower Fence of the sample set.
> Upper Fence = Q3 + 1.5 × Interquartile Range
> Lower Fence = Q1 − 1.5 × Interquartile Range

> -- <cite>[calculatorsoup.com](https://www.calculatorsoup.com/calculators/statistics/mean-median-mode.php)</cite>

# interesting reads
https://kinsta.com/blog/php-benchmarks/
https://www.paulstephenborile.com/2018/03/code-benchmarks-can-measure-fast-software-make-faster/