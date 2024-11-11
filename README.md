# PHP benchmark

Benchmark your php code.

The project is built on top of the original work from Alessandro Torrisi [www.php-benchmark-script.com](http://www.php-benchmark-script.com)

## introduction

    https://www.paulstephenborile.com/2018/03/code-benchmarks-can-measure-fast-software-make-faster/

## compatibility

from php 5.6.40 (use the `php5.6-compatibility` tag) to 8.3.x

## how to use

- git clone the project
- `composer install`

### run standard tests

Standard tests include a bunch of different tests such as `if` / `else`, `loops`, `array` operations, `math` functions, `hashes` and `files`.

    php -d xdebug.mode=off benchmark.php

### run custom test

Custom tests are user fined tests in the `TestUser` class.

- `php -d xdebug.mode=off benchmark.php --custom TestUser --filter ~baseline~` where filter corresponds to a regular expression matching the methods that you want to test. If there are two tests, then the test results will be compared.

## examples

### does xdebug slow down code execution?

ANSWER: yes, from 2x to 7x depending on the test.

    $ php -d xdebug.mode=off benchmark.php --save xdebug_off

    $ php benchmark.php --save xdebug_on

    # compare
    $ php compare.php --file1 benchmark_xdebug_off.txt --file2 benchmark_xdebug_on.txt
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
    [CROPPED]

*NOTE* Since `xdebug` significantly pejorates the results, it's better to turn it off either in `php.ini` or directly when running the command.

    php -d xdebug.mode=off benchmark.php

### does opcache speed up code execution?

ANSWER: It has from no impact to a significant speed increase depending on the test. No impact for `IfElse`, `Hashes` and `Files` and 20% faster for string operations, 34% for loops. If you look at the results carefully, you will notice that the minimum number of iterations drops when opcache is on, which I think is related to building the cache.

    $ php -d xdebug.mode=off -d opcache.enable_cli=0 benchmark.php --save opcache_off
    $ php -d xdebug.mode=off -d opcache.enable_cli=1 benchmark.php --save opcache_on

    php compare.php --file1 benchmark_opcache_off.txt --file2 benchmark_opcache_on.txt

    ----------------------------------------------------------------
    0                   :     testIfElse    testIfElse
    mean                :          49904         52081         +4.4%
    median              :          44143         42948         -2.7%
    mode                :          35174         36360         +3.4%
    minimum             :          26190         13478        -48.5%
    maximum             :          85345         89272         +4.6%
    quartile 1          :          34178         36292         +6.2%
    quartile 3          :          67093         71918         +7.2%
    IQ range            :          32915         35626         +8.2%
    std deviation       :          17626         18951         +7.5%
    normality           :          11.5%         11.5%
    ----------------------------------------------------------------
    1                   :      testLoops     testLoops
    mean                :           8037         10803        +34.4%
    median              :           6729          8939        +32.9%
    mode                :           5588          7770        +39.0%
    minimum             :           3327          3431         +3.1%
    maximum             :          13767         18428        +33.9%
    quartile 1          :           5653          7664        +35.6%
    quartile 3          :          10728         14162        +32.0%
    IQ range            :           5075          6498        +28.0%
    std deviation       :           2824          3813        +35.0%
    normality           :          16.3%         16.3%
    ----------------------------------------------------------------
    2                   :     testArrays    testArrays
    mean                :           4645          4737         +2.0%
    median              :           3726          3871         +3.9%
    mode                :           3972          3562        -10.3%
    minimum             :           2120          1016        -52.1%
    maximum             :           8328          8392         +0.8%
    quartile 1          :           3331          3462         +3.9%
    quartile 3          :           5981          6139         +2.6%
    IQ range            :           2651          2678         +1.0%
    std deviation       :           1674          1679         +0.3%
    normality           :          19.8%         19.8%
    ----------------------------------------------------------------
    3                   :    testStrings   testStrings
    mean                :           2528          3055        +20.9%
    median              :           2031          2459        +21.1%
    mode                :           1843          2223        +20.6%
    minimum             :           1274           741        -41.8%
    maximum             :           4481          5528        +23.4%
    quartile 1          :           1812          2233        +23.3%
    quartile 3          :           3303          3966        +20.1%
    IQ range            :           1491          1733        +16.2%
    std deviation       :            899          1084        +20.6%
    normality           :          19.7%         19.7%
    ----------------------------------------------------------------
    4                   :       testMath      testMath
    mean                :           1357          1490         +9.8%
    median              :           1124          1210         +7.7%
    mode                :            983          1105        +12.4%
    minimum             :            278           225        -19.1%
    maximum             :           2454          2790        +13.7%
    quartile 1          :            965          1084        +12.3%
    quartile 3          :           1769          1899         +7.3%
    IQ range            :            804           816         +1.4%
    std deviation       :            496           552        +11.2%
    normality           :          17.1%         17.1%
    ----------------------------------------------------------------
    5                   :     testHashes    testHashes
    mean                :             96            96          0.0%
    median              :             83            79         -4.8%
    mode                :             68            69         +1.5%
    minimum             :             41            19        -53.7%
    maximum             :            167           170         +1.8%
    quartile 1          :             67            68         +1.5%
    quartile 3          :            125           126         +1.2%
    IQ range            :             58            58         +0.9%
    std deviation       :             35            35         +1.4%
    normality           :          13.4%         13.4%
    ----------------------------------------------------------------
    6                   :      testFiles     testFiles
    mean                :              6             6         -1.4%
    median              :              6             5        -16.7%
    mode                :              4             4          0.0%
    minimum             :              2             3        +50.0%
    maximum             :             10             9        -10.0%
    quartile 1          :              4             4          0.0%
    quartile 3          :              8             8          0.0%
    IQ range            :              4             4          0.0%
    std deviation       :              2             2          0.0%
    normality           :           0.4%          0.4%
    ----------------------------------------------------------------

*NOTE* Since `opcache` can significantly alter the results, it makes sense to set it the same as on your production server.

### is `===` faster than `==`?

```php
...
if ($a == $b) {
    ...
}
...

vs.

...
if ($a === $b) {
    ...
}
...
```

ANSWER: `===` is approximately 12% faster than `==`.

    $ php -d xdebug.mode=off benchmark.php --custom TestUser --filter ~equal~
    PHP benchmark

    ------------------------------------
    platform            :      WINNT x64
    php version         :         8.3.10
    xdebug              :            off
    opcache             :            off
    memory limit        :           512M
    max execution       :              0
    iterations          :            500
    time per iteration  :           20ms
    total time per test :            10s
    ------------------------------------
    ----------------------------------------------------------------
    0                   :         equal1        equal2
    mean                :          44896         50057        +11.5%
    median              :          36732         41129        +12.0%
    mode                :          37953         42447        +11.8%
    minimum             :          20852         25443        +22.0%
    maximum             :          88862         99689        +12.2%
    quartile 1          :          31892         35587        +11.6%
    quartile 3          :          55410         63874        +15.3%
    IQ range            :          23518         28287        +20.3%
    std deviation       :          17966         19912        +10.8%
    normality           :          18.2%         18.2%
    ----------------------------------------------------------------

### is strpos + regex faster than pure regex?

Consider the real life example of parsing an Apache access log for zip file downloads.

    8.8.8.8 - - [01/Dec/2020:06:56:08 +0100] "GET /bin/filev1.048.zip HTTP/2.0" 200 11853462 "
    8.8.8.8 - - [01/Dec/2020:06:56:08 +0100] "GET /css/someotherfile.css HTTP/2.0" 200 11853462 "

Which code is faster?

```php
// regex_1
foreach ($apachelog as $line) {
    $result = preg_match("GET /bin/(.*?)v\d\.\d{3}\.zip~", $line, $matches);
    ...
}

// regex_2
foreach ($apachelog as $line) {
    if (strpos($line, '.zip') !== false) {
        $result = preg_match("GET /bin/(.*?)v\d\.\d{3}\.zip~", $line, $matches);
        ...
    }
}
```

ANSWER: Well the answer is not as obvious as it seems as it depends on the frequency of lines with zip downloads in the Apache log. If every line is a zip download `regex_1` is faster, while if zip downloads are more scarce then `regex_2` becomes faster. Here are the results:

    $ php -d xdebug.mode=off benchmark.php --custom TestUser --filter ~regex~

    # comparison when every line is a zip file
    ---------------------------------------------------
    0                  :     regex_1    regex_2
    mean               :      13762     10609    -22.9%
    median             :      13853     10637    -23.2%
    mode               :      13854     10771    -22.3%
    minmum             :      10473     10096     -3.6%
    maximum            :      14206     10957    -22.9%
    quartile 1         :      13720     10523    -23.3%
    quartile 3         :      13945     10706    -23.2%
    IQ range           :        225       183    -18.7%
    std deviation      :        446       158    -64.5%
    normality          :       0.7%      0.7%
    ---------------------------------------------------

    # results when every 350th line is a zip file
    ---------------------------------------------------
    0                  :     regex_1    regex_2
    mean               :      13058     14034     +7.5%
    median             :      13506     14659     +8.5%
    mode               :      14092     15225     +8.0%
    minmum             :       8520      9024     +5.9%
    maximum            :      14415     15555     +7.9%
    quartile 1         :      12315     12871     +4.5%
    quartile 3         :      14108     15280     +8.3%
    IQ range           :       1793      2409    +34.3%
    std deviation      :       1335      1511    +13.2%
    normality          :       3.3%      3.3%
    ---------------------------------------------------

### is php 8.0 faster than 7.4?

ANSWER: php 8.0 is way faster in tested loops (+73%) and math functions (+17%) and no significant differences in the rest of tests.
_NOTE_: I can no longer reproduce the speed gain for loops as of updating the README.

    $ docker run -it --volume "/$(pwd -W):/test/" php:7.4.12-cli-alpine sh
    $ cd test
    $ php benchmark.php --iterations 700 --time-per-iteration 30 --save php7.4

    $ docker run -it --volume "/$(pwd -W):/test/" php:8.0.0RC5-cli-alpine sh
    $ cd test
    $ php benchmark.php --iterations 700 --time-per-iteration 30 --save php8.0

    $ php compare.php --file1 benchmark_php7.4.txt --file2 benchmark_php8.0.txt
    ------------------------------------------------
    test_if_else
    mean               :    531059   520234    -2.0%
    median             :    548296   537380    -2.0%
    mode               :    561412   547744    -2.4%
    minmum             :    258015   286786    11.2%
    maximum            :    564597   552249    -2.2%
    quartile 1         :    533671   512013    -4.1%
    quartile 3         :    553146   544095    -1.6%
    IQ range           :     19475    32082    64.7%
    std deviation      :     41182    38131    -7.4%
    normality          :      3.1%     3.1%
    ------------------------------------------------
    test_loops
    mean               :     16614    28723    72.9%
    median             :     16912    29317    73.4%
    mode               :     17074    29433    72.4%
    minmum             :     11206    16460    46.9%
    maximum            :     17305    29874    72.6%
    quartile 1         :     16663    28671    72.1%
    quartile 3         :     17030    29540    73.5%
    IQ range           :       366      868   137.0%
    std deviation      :       784     1513    92.9%
    normality          :      1.8%     1.8%
    ------------------------------------------------
    test_arrays
    mean               :      6060     5985    -1.2%
    median             :      6134     6153     0.3%
    mode               :      6141     6218     1.3%
    minmum             :      3939     3930    -0.2%
    maximum            :      6231     6258     0.4%
    quartile 1         :      6077     5913    -2.7%
    quartile 3         :      6165     6203     0.6%
    IQ range           :        88      290   229.5%
    std deviation      :       217      354    63.3%
    normality          :      1.2%     1.2%
    ------------------------------------------------
    test_strings
    mean               :     10543    10395    -1.4%
    median             :     10743    10608    -1.3%
    mode               :     10793    10662    -1.2%
    minmum             :      6233     7403    18.8%
    maximum            :     11047    10905    -1.3%
    quartile 1         :     10563    10341    -2.1%
    quartile 3         :     10844    10734    -1.0%
    IQ range           :       281      393    39.9%
    std deviation      :       591      568    -3.7%
    normality          :      1.9%     1.9%
    ------------------------------------------------
    test_math
    mean               :      5479     6364    16.2%
    median             :      5587     6513    16.6%
    mode               :      5639     6603    17.1%
    minmum             :      3851     3493    -9.3%
    maximum            :      5739     6719    17.1%
    quartile 1         :      5472     6333    15.7%
    quartile 3         :      5645     6583    16.6%
    IQ range           :       172      250    45.2%
    std deviation      :       296      383    29.3%
    normality          :      1.9%     1.9%
    ------------------------------------------------
    test_hashes
    mean               :      1456     1357    -6.8%
    median             :      1485     1453    -2.2%
    mode               :      1498     1544     3.1%
    minmum             :       719      373   -48.1%
    maximum            :      1518     1557     2.6%
    quartile 1         :      1459     1259   -13.7%
    quartile 3         :      1498     1525     1.8%
    IQ range           :        39      266   583.3%
    std deviation      :        80      218   172.1%
    normality          :      1.9%     1.9%
    ------------------------------------------------
    test_files
    mean               :        27       27    -2.0%
    median             :        28       28     0.0%
    mode               :        27       29     7.4%
    minmum             :        16        5   -68.8%
    maximum            :        39       40     2.6%
    quartile 1         :        26       25    -3.8%
    quartile 3         :        30       30     0.0%
    IQ range           :         4        5    25.0%
    std deviation      :         3        4    55.0%
    normality          :      1.3%     1.3%
    ------------------------------------------------

### is php 7.4.12 faster than 5.6.40?

ANSWER: it's 3x - 5x faster accross all tests except hashes where there is a 12% improvement.

    # run test in php 5.6
    $ docker run -it --volume "/$(pwd -W):/test/" php:5.6.40-cli-alpine sh
    $ cd test
    $ php benchmark.php --histogram --show-outliers --show-all --save php5.6_1

    # run test in php 7.4
    $ docker run -it --volume "/$(pwd -W):/test/" php:7.4.12-cli-alpine sh
    $ cd test
    $ php benchmark.php --histogram --show-outliers --show-all --save php7.4.12_1

    # compare
    $ php compare.php --file1 benchmark_php5.6_1_20201201-0441.txt --file2 benchmark_php7.4.12_1_20201201-0447.txt

    ------------------------------------------------
    test_if_else
    mean               :    102215   390219   281.8%
    median             :    105779   404147   282.1%
    mode               :    106411   303052   184.8%
    minmum             :     53583   234585   337.8%
    maximum            :    107758   412297   282.6%
    quartile 1         :    102834   398856   287.9%
    quartile 3         :    106769   406755   281.0%
    IQ range           :      3935     7899   100.7%
    std deviation      :      9422    36180   284.0%
    normality          :      3.4%     3.4%
    ------------------------------------------------
    test_loops
    mean               :      3033    12050   297.3%
    median             :      3127    12431   297.5%
    mode               :      3156    12598   299.2%
    minmum             :      1579     8338   428.1%
    maximum            :      3184    12641   297.0%
    quartile 1         :      3068    12204   297.8%
    quartile 3         :      3155    12554   298.0%
    IQ range           :        87      350   302.3%
    std deviation      :       269      922   242.9%
    normality          :      3.0%     3.0%
    ------------------------------------------------
    test_arrays
    mean               :      1666     4628   177.9%
    median             :      1751     4713   169.2%
    mode               :      1775     4733   166.6%
    minmum             :      1044     3321   218.1%
    maximum            :      1797     4769   165.4%
    quartile 1         :      1621     4661   187.5%
    quartile 3         :      1767     4737   168.1%
    IQ range           :       146       76   -47.9%
    std deviation      :       179      248    38.8%
    normality          :      4.9%     4.9%
    ------------------------------------------------
    test_strings
    mean               :      2164     7462   244.8%
    median             :      2215     7815   252.9%
    mode               :      2188     7958   263.7%
    minmum             :      1064     4043   280.0%
    maximum            :      2293     8066   251.8%
    quartile 1         :      2181     7520   244.8%
    quartile 3         :      2241     7925   253.6%
    IQ range           :        60      405   574.2%
    std deviation      :       182      854   369.8%
    normality          :      2.3%     2.3%
    ------------------------------------------------
    test_math
    mean               :       809     3900   382.0%
    median             :       831     4055   388.3%
    mode               :       889     4083   359.3%
    minmum             :       424     2023   377.1%
    maximum            :       939     4166   343.7%
    quartile 1         :       771     3876   403.0%
    quartile 3         :       888     4114   363.5%
    IQ range           :       117      239   103.8%
    std deviation      :       109      385   252.1%
    normality          :      2.6%     2.6%
    ------------------------------------------------
    test_hashes
    mean               :       921     1033    12.2%
    median             :       971     1087    11.9%
    mode               :       978     1094    11.9%
    minmum             :       500      623    24.6%
    maximum            :      1008     1111    10.2%
    quartile 1         :       898     1051    17.0%
    quartile 3         :       993     1097    10.5%
    IQ range           :        95       46   -51.3%
    std deviation      :       109      116     6.7%
    normality          :      5.2%     5.2%

### Which logger is faster? `Monolog` or `Apix`?

ANSWER: It depends on how the logger is setup but in most cases `Apix` is significantly faster

    $ php -d xdebug.mode=off benchmark.php --custom TestLogger
    PHP benchmark

    -----------------------------------
    platform           :      WINNT x64
    php version        :          8.1.9
    xdebug             :             on
    memory limit       :           512M
    max execution      :              0
    time per iteration :           50ms
    iterations         :            250
    -----------------------------------
    ---------------------------------------------------------------
    0                  : logger_monolog   logger_apix
    mean               :            107          2373      +2108.6%
    median             :            111          2496      +2148.2%
    mode               :             82          2600      +3070.7%
    minimum            :              3           499     +16533.3%
    maximum            :            175          3000      +1614.3%
    quartile 1         :             82          2203      +2586.6%
    quartile 3         :            136          2694      +1880.9%
    IQ range           :             54           491       +809.3%
    std deviation      :             36           476      +1234.2%
    normality          :           3.2%          3.2%
    ---------------------------------------------------------------

## Which templating engine is faster `Twig` or `Latte`?

Speed is the same.

    $ php -d xdebug.mode=off benchmark.php --custom TestTemplates
    PHP benchmark

    ------------------------------------
    platform            :      WINNT x64
    php version         :         8.3.10
    xdebug              :            off
    opcache             :            off
    memory limit        :           512M
    max execution       :              0
    iterations          :            500
    time per iteration  :           20ms
    total time per test :            10s
    ------------------------------------
    ----------------------------------------------------------------
    0                   :       testTwig     testLatte
    mean                :             65            64         -1.5%
    median              :             50            52         +4.0%
    mode                :             49            52         +6.1%
    minimum             :             14            16        +14.3%
    maximum             :            124           123         -0.8%
    quartile 1          :             45            45          0.0%
    quartile 3          :             88            87         -1.1%
    IQ range            :             43            42         -2.3%
    std deviation       :             27            26         -4.4%
    normality           :          22.9%         22.9%
    ----------------------------------------------------------------

## run your own tests

The `TestUser` class serves as a template to create your own tests. To run your tests:

    $ php -d xdebug.mode=off benchmark.php --custom TestUser

## definitions

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

## interesting reads

    https://kinsta.com/blog/php-benchmarks/
    https://www.paulstephenborile.com/2018/03/code-benchmarks-can-measure-fast-software-make-faster/

## todo

- save test conditions - save environment variables and compare them
- show tests in multiple columns
