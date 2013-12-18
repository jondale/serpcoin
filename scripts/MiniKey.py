#!/usr/bin/python
"""
    serpcoin - physical bitcoin storage tools.
    Copyright (C) 2013 Jondale Stratton <btc at serpco dot com> 

    serpcoin is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    serpcoin is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with serpcoin.  If not, see <http://www.gnu.org/licenses/>.
"""

"""
The rules for the mini private key format plus the basis for the key creation code came from:
https://en.bitcoin.it/wiki/Mini_private_key_format
"""

import random
import os
import hashlib
import binascii
import sys


def b58encode(num, pad=''):
    alphabet="123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz"
    out = ''
    while num >= 58:
        num,m = divmod(num, 58)
        out = alphabet[m] + out
    return pad + alphabet[num] + out



class btcMiniKey:

    # The base58 alphabet minus the 1 just to avoid confusion
    # We'll use this alphabet just for the mini key. 
    BASE58_SUBSET = '23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz'
    miniKey = False
    privateKey = False
    privateKeyWIF = False
    publicKey = False
    bitcoinAddress = False

    def Candidate(self):
        """
        Create a possible minikey.

        The private key encoding consists of 30 alphanumeric 
        characters from the base58 alphabet used in Bitcoin. 
        The first of the characters is always the uppercase letter S.

        We use SystemRandom() here to randomly choose from our base58
        alphabet because it uses os.urandom which is more suitable for 
        crypto key generation.
        """
        return('%s%s' % ('S',''.join( [self.BASE58_SUBSET[ random.SystemRandom().randrange(0,len(self.BASE58_SUBSET)) ] for i in range(29)])))
 

    def Validate(self,miniKey):
        """
        Check if mini key is valid.

        First make sure it's the proper 30 characters in length.
        Secondly, make sure the key starts with the capital S.
        Thirdly, make sure the key only consists of our base58 alphabet subset.

        Finally, use the following rules to make sure the key is valid:
          1. Add a question mark to the end of the mini private key string.
          2. Take the SHA256 hash of the entire string. 
          3. The first byte of the hash must be \x00 to be valid.
        """

        # Make sure it is 30 characters
        if len(miniKey) != 30:
            return False

        # Make sure it starts with capital S   
        if miniKey[0] != 'S':
            return False

        # Make sure it uses our base58 alphabet subset
        if not (any (c in set(self.BASE58_SUBSET)) for c in miniKey):
            return False

        # Make sure the hash for the key + ? has the first byte of 00
        if not hashlib.sha256(miniKey+'?').digest()[0] == '\x00':
            return False

        return True 

    def DeducePrivateKey(self,miniKey):
        """ 
        Take the mini private key and deduce the non-mini version along with other formats of the private key.
        """

        self.miniKey = miniKey

        # Private key is just a sha256 of the mini key
        self.privateKey = hashlib.sha256(miniKey).hexdigest()

        # WIF as described https://en.bitcoin.it/wiki/Wallet_export_format 
        chksum = binascii.hexlify(hashlib.sha256(hashlib.sha256(binascii.unhexlify('80'+self.privateKey)).digest()).digest()[:4])
        self.privateKeyWIF = b58encode(long('80'+self.privateKey+chksum, 16))

        return self.privateKey

    def DeducePublicKey(self,privateKey):
        """
        Deduce the public key and bitcoin address from the private key.
        Bitcoin uses Secp256k1 for signing as defined https://en.bitcoin.it/wiki/Secp256k1
        """

        self.privateKey = privateKey

        try:
            import ecdsa

            # secp256k1, http://www.oid-info.com/get/1.3.132.0.10
            _p = 0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2FL
            _r = 0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141L
            _b = 0x0000000000000000000000000000000000000000000000000000000000000007L
            _a = 0x0000000000000000000000000000000000000000000000000000000000000000L
            _Gx = 0x79BE667EF9DCBBAC55A06295CE870B07029BFCDB2DCE28D959F2815B16F81798L
            _Gy = 0x483ada7726a3c4655da4fbfc0e1108a8fd17b448a68554199c47d08ffb10d4b8L
            curve_secp256k1 = ecdsa.ellipticcurve.CurveFp( _p, _a, _b )
            generator_secp256k1 = ecdsa.ellipticcurve.Point( curve_secp256k1, _Gx, _Gy, _r )
            oid_secp256k1 = (1,3,132,0,10)
            SECP256k1 = ecdsa.curves.Curve("SECP256k1", curve_secp256k1, generator_secp256k1, oid_secp256k1 )

            pubkey = chr(4) + ecdsa.SigningKey.from_secret_exponent(long(self.privateKey, 16), curve=SECP256k1 ).get_verifying_key().to_string()
            pad = ""
            rmd = hashlib.new('ripemd160')
            rmd.update(hashlib.sha256(pubkey).digest())
            an = chr(0) + rmd.digest()
            for c in an:
                if c == '\0': pad += '1'
                else: break
            addr = long(binascii.hexlify(an + hashlib.sha256(hashlib.sha256(an).digest()).digest()[0:4]), 16)
            self.publicKey = binascii.hexlify(pubkey).upper()
            self.bitcoinAddress = b58encode(addr,pad)

        except ImportError:
            return False

                        
        return self.publicKey


    def Generate(self):
        """
        Generate a valid mini private key
        """

        while (not self.bitcoinAddress):

            self.miniKey = self.Candidate()
            while not self.Validate(self.miniKey):
                self.miniKey = self.Candidate()
        
            self.DeducePrivateKey(self.miniKey)
            self.DeducePublicKey(self.privateKey)

        return self.miniKey


    def __init__(self):
        self.Generate()



#################################################################################################

if (len(sys.argv) == 2) and (sys.argv[1].isdigit()):

    for x in range(0,int(sys.argv[1])):
        b = btcMiniKey()
        #print " Mini Private Key: " + b.miniKey
        #print "      Private Key: " + b.privateKey
        #print "Private Key (WIF): " + b.privateKeyWIF
        #print "       Public Key: " + b.publicKey
        #print "  Bitcoin Address: " + b.bitcoinAddress
        #print "-----------------------------------------------------------"
        print b.miniKey+":"+b.privateKeyWIF+":"+b.bitcoinAddress

else:

    print "Usage: " + sys.argv[0] + " <# keys>"

