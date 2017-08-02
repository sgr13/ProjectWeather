$(document).ready(function() {
    function showWeather() {
        $.ajax({
            method: 'get',
            url: "/ajax",
            dataType: "json",
            success: function(data) {
                var obj = jQuery.parseJSON(data);
                var cityName = obj.name;

                var dateTime = new Date();
                var minutesTwoDigit = ("0" + dateTime.getMinutes()).substr(-2);
                var hours = dateTime.getHours();
                var time = hours + ":" + minutesTwoDigit;

                var weather = obj.main;
                var temp = weather.temp;
                temp = temp.toFixed(1) + "&deg;C";

                var main = obj.weather;
                var iconPath = main[0].icon + ".png";
                var src = "http://openweathermap.org/img/w/" + iconPath ;
                $("#graphic").attr('src', src);

                $("#time").html(time);
                $("#temp").html(temp);
                $("#city").html(cityName);
            }
        })
    }
    setInterval(showWeather, 100);
});
