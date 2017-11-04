from random import *
def change():
    f = open('C:/Users/K/Desktop/New folder/output.txt', 'w')
    owner={}
    for i in range(1,25):
        temp=randint(1,24)
        owner[i]=[temp]
        f.write('INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES ('+str(temp)+','+str(i)+',\''+str(2*i-1)+'\');\n')
        temp=randint(1,24)
        owner[i]+=[temp]
        f.write('INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES ('+str(temp)+','+str(i)+',\''+str(2*i)+'\');\n')
    f.write('\n')
    
    
    dic = {}
    for i in range(1,25):
        dic[i]=slot(r())
        for pet in dic[i]:
            for s in dic[i][pet]:
                f.write('INSERT INTO availability(start_time, end_time, pcat_id, taker_id) VALUES (\''+date(s[0])+'\',\''+date(s[1])+'\','+str(pet)+','+str(i)+');\n')
    f.write('\n')

    taker={}
    for i in dic:
        for pet in dic[i]:
            for s in dic[i][pet]:
                if pet not in taker:
                    taker[pet]={}
                    taker[pet][i]=[s]
                else:
                    if i not in taker[pet]:
                        taker[pet][i]=[s]
                    else:
                        taker[pet][i]+=[s]

    status=['pending', 'failed', 'successful', 'cancelled']
    id=0
    for i in range(1,25):
        for pet in owner[i]:
            id+=1
            r1=0
            r2=0
            request=[]
            while(r1<10 and r2<100):
                r2+=1
                if(pet not in taker):
                    break
                k=list(taker[pet].keys())
                t=k[randint(0,len(k)-1)]
                s=taker[pet][t]
                slot0=s[randint(0,len(s)-1)]
                start=slot0[0]
                end=slot0[1]
                if(start==end-1):
                    x=start
                else:
                    x=randint(start,end-1)
                if t*100+x not in request and i!=t:
                    r1+=1
                    request+=[t*100+x]
                    f.write('INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id, status) VALUES ('+str(i)+','+str(t)+',\''+date(x)+'\',\''+date(x+1)+'\',\'No\','+str(randint(1,100))+','+str(id)+',\''+status[randint(0,3)]+'\');\n')
                


    f.close()

def date(n):
    if n<10:
        return '2018-01-01 0'+str(n)+':00:00'
    elif n<24:
        return '2018-01-01 '+str(n)+':00:00'
    elif n<34:
        return '2018-01-02 0'+str(n-24)+':00:00'
    else:
        return '2018-01-02 '+str(n-24)+':00:00'

def r():
    result=[]
    while(len(result)<10):
        temp=randint(0,47)
        if(temp not in result):
            result.append(temp)
    result.sort()
    return result

def p():
    result=[]
    while(len(result)<6):
        temp=randint(1,24)
        if(temp not in result):
            result.append(temp)
    result.sort()
    return result

def slot(result):
    ans={}
    pet=p()
    for i in range(6):
        for j in range(5):
            res=r()
            if pet[i] not in ans:
                ans[pet[i]]=[[res[2*j],res[2*j+1]]]
            else:
                ans[pet[i]]+=[[res[2*j],res[2*j+1]]]
    return ans
