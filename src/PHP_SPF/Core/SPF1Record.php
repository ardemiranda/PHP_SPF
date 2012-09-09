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

namespace PHP_SPF\Core;

/**
 * The Class represent the SPF1 Record and provide methods to get all directives
 * and modifiers.
 *
 */
class SPF1Record {

    private $record;
    private $directives = array();
    private $modifiers = array();


    public function __construct($record = null) {
        $this->record = $record;
    }

    /**
     * Return the directives as Collection
     *
     * @return directives Collection of all qualifier+mechanism which should be
     *         used
     */
    public function getDirectives() {
        return $this->directives;
    }

    /**
     * Return the modifiers as Collection
     *
     * @return modifiers Collection of all modifiers which should be used
     */
    public function getModifiers() {
        return $this->modifiers;
    }

    /**
     * @return the record in its string source format
     */
    public function getRecord() {
        return $this->record;
    }

    /**
     * Return a single iterator over Directives and Modifiers
     *
     * @return a chained iterator of the terms
     */
    public function iterator() {
        throw new \Execeptgion("implementar");
        return array(); /*new Iterator() {
            boolean first = true;
            Iterator current = getDirectives().iterator();

            public boolean hasNext() {
                if (current.hasNext()) {
                    return true;
                } else if (first) {
                    current = getModifiers().iterator();
                    first = false;
                    return current.hasNext();
                } else return false;
            }

            public Object next() {
                return current.next();
            }

            public void remove() {
                throw new UnsupportedOperationException("Readonly iterator");
            }

        };*/
    }
}
