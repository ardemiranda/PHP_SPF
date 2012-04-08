<?php

/****************************************************************
 * Licensed to the Apache Software Foundation (ASF) under one   *
* or more contributor license agreements.  See the NOTICE file *
* distributed with this work for additional information        *
* regarding copyright ownership.  The ASF licenses this file   *
* to you under the Apache License, Version 2.0 (the            *
        * "License"); you may not use this file except in compliance   *
* with the License.  You may obtain a copy of the License at   *
*                                                              *
*   http://www.apache.org/licenses/LICENSE-2.0                 *
*                                                              *
* Unless required by applicable law or agreed to in writing,   *
* software distributed under the License is distributed on an  *
* "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY       *
* KIND, either express or implied.  See the License for the    *
* specific language governing permissions and limitations      *
* under the License.                                           *
****************************************************************/

namespace Tester\PHP_SPF;

class DNSTestingServer implements ResponseGenerator {

    const FLAG_DNSSECOK = 1;

    const FLAG_SIGONLY = 2;

    protected $zone;

    private $timeoutServers;

    public $random;// = new Random();

    public function __construct($address = null, $porta = null) {

        $port = (int) ($porta != null ? $porta : "53");
        $addr = $address != null ? address : "0.0.0.0";

        $t;
        $t = new Thread(new TCPListener(addr, port.intValue(), this));
        $t.setDaemon(true);
        $t.start();

        $t = new Thread(new UDPListener(addr, port.intValue(), this));
        $t.setDaemon(true);
        $t.start();

        $this->zone = null;
    }

    public function setData(array $map) {
        try {
            $this->timeoutServers = array();
            $records = array();

            $records.add(new SOARecord(Name.root, DClass.IN, 3600, Name.root,
                    Name.root, 857623948, 0, 0, 0, 0));
            records.add(new NSRecord(Name.root, DClass.IN, 3600, Name.root));

            $hosts = map.keySet().iterator();
            while (hosts.hasNext()) {
                $host = (String) hosts.next();
                $hostname;
                if (! $host.endsWith(".")) {
                    $hostname = Name.fromString(host + ".");
                } else {
                    $hostname = Name.fromString(host);
                }

                $l = map.get(host);
                if ($l != null)
                    for ($i = $l.iterator(); $i.hasNext();) {
                        $o = i.next();
                        if ($o instanceof Map) {
                            $hm = $o;

                            $types = hm.keySet().iterator();

                            while ($types.hasNext()) {
                                $type = (String) types.next();
                                if ("MX" === $type) {
                                    $mxList = $hm.get(type);
                                    $mxs = $mxList.iterator();
                                    while ($mxs.hasNext()) {
                                        $prio = $mxs.next();
                                        $cname = $mxs.next();
                                        if ($cname != null) {
                                            if ($cname.length() > 0 &&  !cname.endsWith(".")) {
                                                $cname .= ".";
                                            }
                                            $records.add(new \Net_DNS2_RR_MX(hostname,
                                                    DClass.IN, 3600, prio
                                                            .intValue(), Name
                                                            .fromString(cname)));
                                        }
                                    }
                                } else {
                                    Object value = hm.get(type);
                                    if ("A".equals(type)) {
                                        records.add(new ARecord(hostname,
                                                DClass.IN, 3600, Address
                                                        .getByAddress((String) value)));
                                    } else if ("AAAA".equals(type)) {
                                        records.add(new AAAARecord(hostname,
                                                DClass.IN, 3600, Address
                                                        .getByAddress((String) value)));
                                    } else if ("SPF".equals(type)) {
                                        if (value instanceof List<?>) {
                                            records.add(new SPFRecord(hostname,
                                                    DClass.IN, 3600, (List<?>) value));
                                        } else {
                                            records.add(new SPFRecord(hostname,
                                                    DClass.IN, 3600, (String) value));
                                        }
                                    } else if ("TXT".equals(type)) {
                                        if (value instanceof List<?>) {
                                            records.add(new TXTRecord(hostname,
                                                    DClass.IN, 3600, (List<?>) value));
                                        } else {
                                            records.add(new TXTRecord(hostname,
                                                    DClass.IN, 3600, (String) value));
                                        }
                                    } else {
                                        if (!((String) value).endsWith(".")) {
                                            value = ((String) value)+".";
                                        }
                                        if ("PTR".equals(type)) {
                                            records
                                                    .add(new PTRRecord(
                                                            hostname,
                                                            DClass.IN,
                                                            3600,
                                                            Name
                                                                    .fromString((String) value)));
                                        } else if ("CNAME".equals(type)) {
                                            records.add(new CNAMERecord(
                                                    hostname, DClass.IN, 3600,
                                                    Name.fromString((String) value)));
                                        } else {
                                            throw new IllegalStateException(
                                                    "Unsupported type: " + type);
                                        }
                                    }
                                }
                            }
                        } else if ("TIMEOUT".equals(o)) {
                            timeoutServers.add(hostname);
                        } else {
                            throw new IllegalStateException(
                                    "getRecord found an unexpected data");
                        }
                    }
            }

            zone = new Zone(Name.root, (Record[]) records
                    .toArray(new Record[] {}));

        } catch (TextParseException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
        } catch (UnknownHostException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
        } catch (IOException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
        }
    }

    private SOARecord findSOARecord() {
        return zone.getSOA();
    }

    private RRset findNSRecords() {
        return zone.getNS();
    }

    // TODO verify why enabling this lookup will make some test to fail!
    private RRset findARecord(Name name) {
        return null;
        //return zone.findExactMatch(name, Type.A);
    }

    private SetResponse findRecords(Name name, int type) {
        SetResponse sr = zone.findRecords(name, type);

        if (sr == null || sr.answers() == null || sr.answers().length == 0) {
            boolean timeout = timeoutServers.contains(name);
            if (timeout) {
                try {
                    Thread.sleep(2100);
                }
                catch (InterruptedException e) {
                }
                return null;
            }
        }

        try {
            Thread.sleep(random.nextInt(500));
        }
        catch (Exception e) {}

        return sr;
    }

    @SuppressWarnings("unchecked")
    void addRRset(Name name, Message response, RRset rrset, int section,
            int flags) {
        for (int s = 1; s <= section; s++)
            if (response.findRRset(name, rrset.getType(), s))
                return;
        if ((flags & FLAG_SIGONLY) == 0) {
            Iterator<Record> it = rrset.rrs();
            while (it.hasNext()) {
                Record r = (Record) it.next();
                if (r.getName().isWild() && !name.isWild())
                    r = r.withName(name);
                response.addRecord(r, section);
            }
        }
        if ((flags & (FLAG_SIGONLY | FLAG_DNSSECOK)) != 0) {
            Iterator it = rrset.sigs();
            while (it.hasNext()) {
                Record r = (Record) it.next();
                if (r.getName().isWild() && !name.isWild())
                    r = r.withName(name);
                response.addRecord(r, section);
            }
        }
    }

    private void addGlue(Message response, Name name, int flags) {
        RRset a = findARecord(name);
        if (a == null)
            return;
        addRRset(name, response, a, Section.ADDITIONAL, flags);
    }

    private void addAdditional2(Message response, int section, int flags) {
        Record[] records = response.getSectionArray(section);
        for (int i = 0; i < records.length; i++) {
            Record r = records[i];
            Name glueName = r.getAdditionalName();
            if (glueName != null)
                addGlue(response, glueName, flags);
        }
    }

    private final void addAdditional(Message response, int flags) {
        addAdditional2(response, Section.ANSWER, flags);
        addAdditional2(response, Section.AUTHORITY, flags);
    }

    byte addAnswer(Message response, Name name, int type, int dclass,
            int iterations, int flags) {
        SetResponse sr;
        byte rcode = Rcode.NOERROR;

        if (iterations > 6)
            return Rcode.NOERROR;

        if (type == Type.SIG || type == Type.RRSIG) {
            type = Type.ANY;
            flags |= FLAG_SIGONLY;
        }

        sr = findRecords(name, type);

        // TIMEOUT
        if (sr == null) {
            return -1;
        }

        if (sr.isNXDOMAIN() || sr.isNXRRSET()) {
            if (sr.isNXDOMAIN())
                response.getHeader().setRcode(Rcode.NXDOMAIN);

            response.addRecord(findSOARecord(), Section.AUTHORITY);

            if (iterations == 0)
                response.getHeader().setFlag(Flags.AA);

            rcode = Rcode.NXDOMAIN;

        } else if (sr.isDelegation()) {
            RRset nsRecords = sr.getNS();
            addRRset(nsRecords.getName(), response, nsRecords,
                    Section.AUTHORITY, flags);
        } else if (sr.isCNAME()) {
            CNAMERecord cname = sr.getCNAME();
            RRset rrset = new RRset(cname);
            addRRset(name, response, rrset, Section.ANSWER, flags);
            if (iterations == 0)
                response.getHeader().setFlag(Flags.AA);
            rcode = addAnswer(response, cname.getTarget(), type, dclass,
                    iterations + 1, flags);
        } else if (sr.isDNAME()) {
            DNAMERecord dname = sr.getDNAME();
            RRset rrset = new RRset(dname);
            addRRset(name, response, rrset, Section.ANSWER, flags);
            Name newname;
            try {
                newname = name.fromDNAME(dname);
            } catch (NameTooLongException e) {
                return Rcode.YXDOMAIN;
            }
            rrset = new RRset(new CNAMERecord(name, dclass, 0, newname));
            addRRset(name, response, rrset, Section.ANSWER, flags);
            if (iterations == 0)
                response.getHeader().setFlag(Flags.AA);
            rcode = addAnswer(response, newname, type, dclass, iterations + 1,
                    flags);
        } else if (sr.isSuccessful()) {
            RRset[] rrsets = sr.answers();
            for (int i = 0; i < rrsets.length; i++)
                addRRset(name, response, rrsets[i], Section.ANSWER, flags);

            RRset findNSRecords = findNSRecords();
            addRRset(findNSRecords.getName(), response, findNSRecords,
                    Section.AUTHORITY, flags);

            if (iterations == 0)
                response.getHeader().setFlag(Flags.AA);
        }
        return rcode;
    }

    public byte[] generateReply(Message query, int length, Socket s)
            throws IOException {
        Header header;
        int maxLength;
        int flags = 0;

        header = query.getHeader();
        if (header.getFlag(Flags.QR))
            return null;
        if (header.getRcode() != Rcode.NOERROR)
            return errorMessage(query, Rcode.FORMERR);
        if (header.getOpcode() != Opcode.QUERY)
            return errorMessage(query, Rcode.NOTIMP);

        Record queryRecord = query.getQuestion();

        OPTRecord queryOPT = query.getOPT();
        if (queryOPT != null && queryOPT.getVersion() > 0) {
        }

        if (s != null)
            maxLength = 65535;
        else if (queryOPT != null)
            maxLength = Math.max(queryOPT.getPayloadSize(), 512);
        else
            maxLength = 512;

        if (queryOPT != null && (queryOPT.getFlags() & ExtendedFlags.DO) != 0)
            flags = FLAG_DNSSECOK;

        Message response = new Message(query.getHeader().getID());
        response.getHeader().setFlag(Flags.QR);
        if (query.getHeader().getFlag(Flags.RD))
            response.getHeader().setFlag(Flags.RD);
        response.addRecord(queryRecord, Section.QUESTION);

        Name name = queryRecord.getName();
        int type = queryRecord.getType();
        int dclass = queryRecord.getDClass();
        if (!Type.isRR(type) && type != Type.ANY)
            return errorMessage(query, Rcode.NOTIMP);

        byte rcode = addAnswer(response, name, type, dclass, 0, flags);

        // TIMEOUT
        if (rcode == -1) {
            return null;
        }

        if (rcode != Rcode.NOERROR && rcode != Rcode.NXDOMAIN)
            return errorMessage(query, rcode);

        addAdditional(response, flags);

        if (queryOPT != null) {
            int optflags = (flags == FLAG_DNSSECOK) ? ExtendedFlags.DO : 0;
            OPTRecord opt = new OPTRecord((short) 4096, rcode, (byte) 0,
                    optflags);
            response.addRecord(opt, Section.ADDITIONAL);
        }

        return response.toWire(maxLength);
    }

    byte[] buildErrorMessage(Header header, int rcode, Record question) {
        Message response = new Message();
        response.setHeader(header);
        for (int i = 0; i < 4; i++)
            response.removeAllRecords(i);
        if (rcode == Rcode.SERVFAIL)
            response.addRecord(question, Section.QUESTION);
        header.setRcode(rcode);
        return response.toWire();
    }

    public byte[] formerrMessage(byte[] in) {
        Header header;
        try {
            header = new Header(in);
        } catch (IOException e) {
            return null;
        }
        return buildErrorMessage(header, Rcode.FORMERR, null);
    }

    public byte[] errorMessage(Message query, int rcode) {
        return buildErrorMessage(query.getHeader(), rcode, query.getQuestion());
    }

    public byte[] generateReply(byte[] in, int length) {
        Message query;
        byte[] response = null;
        try {
            query = new Message(in);
            response = generateReply(query, length, null);
        } catch (IOException e) {
            response = formerrMessage(in);
        }
        return response;
    }

}
