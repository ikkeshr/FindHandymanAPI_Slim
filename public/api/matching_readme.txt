MATCH_SCORE

h = handyman_id, service_id, day_name, start_time, end_time, adress_lat, address_lng, start_price, end_price

j = service_id, date, budget, address_lat, address_lng, time


s,d,b,a,t

if (h.service_id == j.service_id) {
    s = 1
} else {
    s = 0
}

if ( DAYNAME(j.date) == h.day_name ) {
    d = 1
} else {
    d = 0
}

b = (j.budget - h.start_time) / budget

a = 1 / HAVERSINE(j.address_lat, j.address_lng, h.address_lat, h.address_lng)

t = 1 / (j.time - h.start_time)

score = s * ( d*(t+4) + (b*3) + (a*2) )

