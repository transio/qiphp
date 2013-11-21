<?php
namespace Qi\Http;

/**
 * An enum of HTTPscheme constants.
 */
class Scheme {
    const HTTP = "http";
    const HTTPS = "https";
    const FTP = "ftp";
    const FTPS = "ftps";
    const FIlE = "file";
    const MAILTO = "mailto";
    const URN = "urn";
    const TEL = "tel";
    const RTSP = "rtsp";
    const RTP = "rtp";
    
    private function __construct() {}
}
