<?php

use Codeception\Util\Fixtures;
use Grav\Common\Grav;
use Grav\Common\Uri;
use Grav\Common\Utils;

/**
 * Class UtilsTest
 */
class UtilsTest extends \Codeception\TestCase\Test
{
    /** @var Grav $grav */
    protected $grav;

    /** @var Uri $uri */
    protected $uri;

    protected function _before()
    {
        $grav = Fixtures::get('grav');
        $this->grav = $grav();
        $this->uri = $this->grav['uri'];
    }

    protected function _after()
    {
    }

    public function testStartsWith()
    {
        $this->assertTrue(Utils::startsWith('english', 'en'));
        $this->assertTrue(Utils::startsWith('English', 'En'));
        $this->assertTrue(Utils::startsWith('ENGLISH', 'EN'));
        $this->assertTrue(Utils::startsWith(
            'ENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISH',
            'EN'
        ));

        $this->assertFalse(Utils::startsWith('english', 'En'));
        $this->assertFalse(Utils::startsWith('English', 'EN'));
        $this->assertFalse(Utils::startsWith('ENGLISH', 'en'));
        $this->assertFalse(Utils::startsWith(
            'ENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISH',
            'e'
        ));

        $this->assertTrue(Utils::startsWith('english', 'En', false));
        $this->assertTrue(Utils::startsWith('English', 'EN', false));
        $this->assertTrue(Utils::startsWith('ENGLISH', 'en', false));
        $this->assertTrue(Utils::startsWith(
            'ENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISH',
            'e',
            false
        ));
    }

    public function testEndsWith()
    {
        $this->assertTrue(Utils::endsWith('english', 'sh'));
        $this->assertTrue(Utils::endsWith('EngliSh', 'Sh'));
        $this->assertTrue(Utils::endsWith('ENGLISH', 'SH'));
        $this->assertTrue(Utils::endsWith(
            'ENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISH',
            'ENGLISH'
        ));

        $this->assertFalse(Utils::endsWith('english', 'de'));
        $this->assertFalse(Utils::endsWith('EngliSh', 'sh'));
        $this->assertFalse(Utils::endsWith('ENGLISH', 'Sh'));
        $this->assertFalse(Utils::endsWith(
            'ENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISH',
            'DEUSTCH'
        ));

        $this->assertTrue(Utils::endsWith('english', 'SH', false));
        $this->assertTrue(Utils::endsWith('EngliSh', 'sH', false));
        $this->assertTrue(Utils::endsWith('ENGLISH', 'sh', false));
        $this->assertTrue(Utils::endsWith(
            'ENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISH',
            'english',
            false
        ));
    }

    public function testContains()
    {
        $this->assertTrue(Utils::contains('english', 'nglis'));
        $this->assertTrue(Utils::contains('EngliSh', 'gliSh'));
        $this->assertTrue(Utils::contains('ENGLISH', 'ENGLI'));
        $this->assertTrue(Utils::contains(
            'ENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISH',
            'ENGLISH'
        ));

        $this->assertFalse(Utils::contains('EngliSh', 'GLI'));
        $this->assertFalse(Utils::contains('EngliSh', 'English'));
        $this->assertFalse(Utils::contains('ENGLISH', 'SCH'));
        $this->assertFalse(Utils::contains(
            'ENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISH',
            'DEUSTCH'
        ));

        $this->assertTrue(Utils::contains('EngliSh', 'GLI', false));
        $this->assertTrue(Utils::contains('EngliSh', 'ENGLISH', false));
        $this->assertTrue(Utils::contains('ENGLISH', 'ish', false));
        $this->assertTrue(Utils::contains(
            'ENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISHENGLISH',
            'english',
            false
        ));
    }

    public function testSubstrToString()
    {
        $this->assertEquals('en', Utils::substrToString('english', 'glish'));
        $this->assertEquals('english', Utils::substrToString('english', 'test'));
        $this->assertNotEquals('en', Utils::substrToString('english', 'lish'));

        $this->assertEquals('en', Utils::substrToString('english', 'GLISH', false));
        $this->assertEquals('english', Utils::substrToString('english', 'TEST', false));
        $this->assertNotEquals('en', Utils::substrToString('english', 'LISH', false));
    }

    public function testMergeObjects()
    {
        $obj1 = new stdClass();
        $obj1->test1 = 'x';
        $obj2 = new stdClass();
        $obj2->test2 = 'y';

        $objMerged = Utils::mergeObjects($obj1, $obj2);

        $this->assertObjectHasAttribute('test1', $objMerged);
        $this->assertObjectHasAttribute('test2', $objMerged);
    }

    public function testDateFormats()
    {
        $dateFormats = Utils::dateFormats();
        $this->assertIsArray($dateFormats);
        $this->assertContainsOnly('string', $dateFormats);

        $default_format = $this->grav['config']->get('system.pages.dateformat.default');

        if ($default_format !== null) {
            $this->assertArrayHasKey($default_format, $dateFormats);
        }
    }

    public function testTruncate()
    {
        $this->assertEquals('engli' . '&hellip;', Utils::truncate('english', 5));
        $this->assertEquals('english', Utils::truncate('english'));
        $this->assertEquals('This is a string to truncate', Utils::truncate('This is a string to truncate'));
        $this->assertEquals('Th' . '&hellip;', Utils::truncate('This is a string to truncate', 2));
        $this->assertEquals('engli' . '...', Utils::truncate('english', 5, true, " ", "..."));
        $this->assertEquals('english', Utils::truncate('english'));
        $this->assertEquals('This is a string to truncate', Utils::truncate('This is a string to truncate'));
        $this->assertEquals('This' . '&hellip;', Utils::truncate('This is a string to truncate', 3, true));
        $this->assertEquals('<input' . '&hellip;', Utils::truncate('<input type="file" id="file" multiple />', 6, true));
    }

    public function testSafeTruncate()
    {
        $this->assertEquals('This' . '&hellip;', Utils::safeTruncate('This is a string to truncate', 1));
        $this->assertEquals('This' . '&hellip;', Utils::safeTruncate('This is a string to truncate', 4));
        $this->assertEquals('This is' . '&hellip;', Utils::safeTruncate('This is a string to truncate', 5));
    }

    public function testTruncateHtml()
    {
        $this->assertEquals('T...', Utils::truncateHtml('This is a string to truncate', 1));
        $this->assertEquals('This is...', Utils::truncateHtml('This is a string to truncate', 7));
        $this->assertEquals('<p>T...</p>', Utils::truncateHtml('<p>This is a string to truncate</p>', 1));
        $this->assertEquals('<p>This...</p>', Utils::truncateHtml('<p>This is a string to truncate</p>', 4));
        $this->assertEquals('<p>This is a...</p>', Utils::truncateHtml('<p>This is a string to truncate</p>', 10));
        $this->assertEquals('<p>This is a string to truncate</p>', Utils::truncateHtml('<p>This is a string to truncate</p>', 100));
        $this->assertEquals('<input type="file" id="file" multiple />', Utils::truncateHtml('<input type="file" id="file" multiple />', 6));
        $this->assertEquals('<ol><li>item 1 <i>so...</i></li></ol>', Utils::truncateHtml('<ol><li>item 1 <i>something</i></li><li>item 2 <strong>bold</strong></li></ol>', 10));
        $this->assertEquals("<p>This is a string.</p>\n<p>It splits two lines.</p>", Utils::truncateHtml("<p>This is a string.</p>\n<p>It splits two lines.</p>", 100));
    }

    public function testSafeTruncateHtml()
    {
        $this->assertEquals('This...', Utils::safeTruncateHtml('This is a string to truncate', 1));
        $this->assertEquals('This is a...', Utils::safeTruncateHtml('This is a string to truncate', 3));
        $this->assertEquals('<p>This...</p>', Utils::safeTruncateHtml('<p>This is a string to truncate</p>', 1));
        $this->assertEquals('<p>This is...</p>', Utils::safeTruncateHtml('<p>This is a string to truncate</p>', 2));
        $this->assertEquals('<p>This is a string to...</p>', Utils::safeTruncateHtml('<p>This is a string to truncate</p>', 5));
        $this->assertEquals('<p>This is a string to truncate</p>', Utils::safeTruncateHtml('<p>This is a string to truncate</p>', 20));
        $this->assertEquals('<input type="file" id="file" multiple />', Utils::safeTruncateHtml('<input type="file" id="file" multiple />', 6));
        $this->assertEquals('<ol><li>item 1 <i>something</i></li><li>item 2...</li></ol>', Utils::safeTruncateHtml('<ol><li>item 1 <i>something</i></li><li>item 2 <strong>bold</strong></li></ol>', 5));
    }

    public function testGenerateRandomString()
    {
        $this->assertNotEquals(Utils::generateRandomString(), Utils::generateRandomString());
        $this->assertNotEquals(Utils::generateRandomString(20), Utils::generateRandomString(20));
    }

    public function download()
    {
    }

    public function testGetMimeByExtension()
    {
        $this->assertEquals('application/octet-stream', Utils::getMimeByExtension(''));
        $this->assertEquals('text/html', Utils::getMimeByExtension('html'));
        $this->assertEquals('application/json', Utils::getMimeByExtension('json'));
        $this->assertEquals('application/atom+xml', Utils::getMimeByExtension('atom'));
        $this->assertEquals('application/rss+xml', Utils::getMimeByExtension('rss'));
        $this->assertEquals('image/jpeg', Utils::getMimeByExtension('jpg'));
        $this->assertEquals('image/png', Utils::getMimeByExtension('png'));
        $this->assertEquals('text/plain', Utils::getMimeByExtension('txt'));
        $this->assertEquals('application/msword', Utils::getMimeByExtension('doc'));
        $this->assertEquals('application/octet-stream', Utils::getMimeByExtension('foo'));
        $this->assertEquals('foo/bar', Utils::getMimeByExtension('foo', 'foo/bar'));
        $this->assertEquals('text/html', Utils::getMimeByExtension('foo', 'text/html'));
    }

    public function testGetExtensionByMime()
    {
        $this->assertEquals('html', Utils::getExtensionByMime('*/*'));
        $this->assertEquals('html', Utils::getExtensionByMime('text/*'));
        $this->assertEquals('html', Utils::getExtensionByMime('text/html'));
        $this->assertEquals('json', Utils::getExtensionByMime('application/json'));
        $this->assertEquals('atom', Utils::getExtensionByMime('application/atom+xml'));
        $this->assertEquals('rss', Utils::getExtensionByMime('application/rss+xml'));
        $this->assertEquals('jpg', Utils::getExtensionByMime('image/jpeg'));
        $this->assertEquals('png', Utils::getExtensionByMime('image/png'));
        $this->assertEquals('txt', Utils::getExtensionByMime('text/plain'));
        $this->assertEquals('doc', Utils::getExtensionByMime('application/msword'));
        $this->assertEquals('html', Utils::getExtensionByMime('foo/bar'));
        $this->assertEquals('baz', Utils::getExtensionByMime('foo/bar', 'baz'));
    }

    public function testNormalizePath()
    {
        $this->assertEquals('/test', Utils::normalizePath('/test'));
        $this->assertEquals('test', Utils::normalizePath('test'));
        $this->assertEquals('test', Utils::normalizePath('../test'));
        $this->assertEquals('/test', Utils::normalizePath('/../test'));
        $this->assertEquals('/test2', Utils::normalizePath('/test/../test2'));
        $this->assertEquals('/test3', Utils::normalizePath('/test/../test2/../test3'));

        $this->assertEquals('//cdnjs.cloudflare.com/ajax/libs/Leaflet.awesome-markers/2.0.2/leaflet.awesome-markers.css', Utils::normalizePath('//cdnjs.cloudflare.com/ajax/libs/Leaflet.awesome-markers/2.0.2/leaflet.awesome-markers.css'));
        $this->assertEquals('//use.fontawesome.com/releases/v5.8.1/css/all.css', Utils::normalizePath('//use.fontawesome.com/releases/v5.8.1/css/all.css'));
        $this->assertEquals('//use.fontawesome.com/releases/v5.8.1/webfonts/fa-brands-400.eot', Utils::normalizePath('//use.fontawesome.com/releases/v5.8.1/css/../webfonts/fa-brands-400.eot'));

        $this->assertEquals('http://cdnjs.cloudflare.com/ajax/libs/Leaflet.awesome-markers/2.0.2/leaflet.awesome-markers.css', Utils::normalizePath('http://cdnjs.cloudflare.com/ajax/libs/Leaflet.awesome-markers/2.0.2/leaflet.awesome-markers.css'));
        $this->assertEquals('http://use.fontawesome.com/releases/v5.8.1/css/all.css', Utils::normalizePath('http://use.fontawesome.com/releases/v5.8.1/css/all.css'));
        $this->assertEquals('http://use.fontawesome.com/releases/v5.8.1/webfonts/fa-brands-400.eot', Utils::normalizePath('http://use.fontawesome.com/releases/v5.8.1/css/../webfonts/fa-brands-400.eot'));

        $this->assertEquals('https://cdnjs.cloudflare.com/ajax/libs/Leaflet.awesome-markers/2.0.2/leaflet.awesome-markers.css', Utils::normalizePath('https://cdnjs.cloudflare.com/ajax/libs/Leaflet.awesome-markers/2.0.2/leaflet.awesome-markers.css'));
        $this->assertEquals('https://use.fontawesome.com/releases/v5.8.1/css/all.css', Utils::normalizePath('https://use.fontawesome.com/releases/v5.8.1/css/all.css'));
        $this->assertEquals('https://use.fontawesome.com/releases/v5.8.1/webfonts/fa-brands-400.eot', Utils::normalizePath('https://use.fontawesome.com/releases/v5.8.1/css/../webfonts/fa-brands-400.eot'));
    }

    public function testIsFunctionDisabled()
    {
        $disabledFunctions = explode(',', ini_get('disable_functions'));

        if ($disabledFunctions[0]) {
            $this->assertEquals(Utils::isFunctionDisabled($disabledFunctions[0]), true);
        }
    }

    public function testTimezones()
    {
        $timezones = Utils::timezones();

        $this->assertIsArray($timezones);
        $this->assertContainsOnly('string', $timezones);
    }

    public function testArrayFilterRecursive()
    {
        $array = [
            'test'  => '',
            'test2' => 'test2'
        ];

        $array = Utils::arrayFilterRecursive($array, function ($k, $v) {
            return !(is_null($v) || $v === '');
        });

        $this->assertContainsOnly('string', $array);
        $this->assertArrayNotHasKey('test', $array);
        $this->assertArrayHasKey('test2', $array);
        $this->assertEquals('test2', $array['test2']);
    }

    public function testPathPrefixedByLangCode()
    {
        $languagesEnabled = $this->grav['config']->get('system.languages.supported', []);
        $arrayOfLanguages = ['en', 'de', 'it', 'es', 'dk', 'el'];
        $languagesNotEnabled = array_diff($arrayOfLanguages, $languagesEnabled);
        $oneLanguageNotEnabled = reset($languagesNotEnabled);

        if (count($languagesEnabled)) {
            $this->assertTrue(Utils::pathPrefixedByLangCode('/' . $languagesEnabled[0] . '/test'));
        }

        $this->assertFalse(Utils::pathPrefixedByLangCode('/' . $oneLanguageNotEnabled . '/test'));
        $this->assertFalse(Utils::pathPrefixedByLangCode('/test'));
        $this->assertFalse(Utils::pathPrefixedByLangCode('/xx'));
        $this->assertFalse(Utils::pathPrefixedByLangCode('/xx/'));
        $this->assertFalse(Utils::pathPrefixedByLangCode('/'));
    }

    public function testDate2timestamp()
    {
        $timestamp = strtotime('10 September 2000');
        $this->assertSame($timestamp, Utils::date2timestamp('10 September 2000'));
        $this->assertSame($timestamp, Utils::date2timestamp('2000-09-10 00:00:00'));
    }

    public function testResolve()
    {
        $array = [
            'test' => [
                'test2' => 'test2Value'
            ]
        ];

        $this->assertEquals('test2Value', Utils::resolve($array, 'test.test2'));
    }

    public function testGetDotNotation()
    {
        $array = [
            'test' => [
                'test2' => 'test2Value',
                'test3' => [
                    'test4' => 'test4Value'
                ]
            ]
        ];

        $this->assertEquals('test2Value', Utils::getDotNotation($array, 'test.test2'));
        $this->assertEquals('test4Value', Utils::getDotNotation($array, 'test.test3.test4'));
        $this->assertEquals('defaultValue', Utils::getDotNotation($array, 'test.non_existent', 'defaultValue'));
    }

    public function testSetDotNotation()
    {
        $array = [
            'test' => [
                'test2' => 'test2Value',
                'test3' => [
                    'test4' => 'test4Value'
                ]
            ]
        ];

        $new = [
            'test1' => 'test1Value'
        ];

        Utils::setDotNotation($array, 'test.test3.test4', $new);
        $this->assertEquals('test1Value', $array['test']['test3']['test4']['test1']);
    }

    public function testIsPositive()
    {
        $this->assertTrue(Utils::isPositive(true));
        $this->assertTrue(Utils::isPositive(1));
        $this->assertTrue(Utils::isPositive('1'));
        $this->assertTrue(Utils::isPositive('yes'));
        $this->assertTrue(Utils::isPositive('on'));
        $this->assertTrue(Utils::isPositive('true'));
        $this->assertFalse(Utils::isPositive(false));
        $this->assertFalse(Utils::isPositive(0));
        $this->assertFalse(Utils::isPositive('0'));
        $this->assertFalse(Utils::isPositive('no'));
        $this->assertFalse(Utils::isPositive('off'));
        $this->assertFalse(Utils::isPositive('false'));
        $this->assertFalse(Utils::isPositive('some'));
        $this->assertFalse(Utils::isPositive(2));
    }

    public function testGetNonce()
    {
        $this->assertIsString(Utils::getNonce('test-action'));
        $this->assertIsString(Utils::getNonce('test-action', true));
        $this->assertSame(Utils::getNonce('test-action'), Utils::getNonce('test-action'));
        $this->assertNotSame(Utils::getNonce('test-action'), Utils::getNonce('test-action2'));
    }

    public function testVerifyNonce()
    {
        $this->assertTrue(Utils::verifyNonce(Utils::getNonce('test-action'), 'test-action'));
    }

    public function testUrl()
    {
        $this->uri->initializeWithUrl('http://testing.dev/path1/path2')->init();

        // Fail hard
        $this->assertSame(false, Utils::url('', true));
        $this->assertSame(false, Utils::url(''));
        $this->assertSame(false, Utils::url(new stdClass()));
        $this->assertSame(false, Utils::url(['foo','bar','baz']));
        $this->assertSame(false, Utils::url('user://does/not/exist'));

        // Fail Gracefully
        $this->assertSame('/', Utils::url('/', false, true));
        $this->assertSame('/', Utils::url('', false, true));
        $this->assertSame('/', Utils::url(new stdClass(), false, true));
        $this->assertSame('/', Utils::url(['foo','bar','baz'], false, true));
        $this->assertSame('/user/does/not/exist', Utils::url('user://does/not/exist', false, true));

        // Simple paths
        $this->assertSame('/', Utils::url('/'));
        $this->assertSame('/path1', Utils::url('/path1'));
        $this->assertSame('/path1/path2', Utils::url('/path1/path2'));
        $this->assertSame('/random/path1/path2', Utils::url('/random/path1/path2'));
        $this->assertSame('/foobar.jpg', Utils::url('/foobar.jpg'));
        $this->assertSame('/path1/foobar.jpg', Utils::url('/path1/foobar.jpg'));
        $this->assertSame('/path1/path2/foobar.jpg', Utils::url('/path1/path2/foobar.jpg'));
        $this->assertSame('/random/path1/path2/foobar.jpg', Utils::url('/random/path1/path2/foobar.jpg'));

        // Simple paths with domain
        $this->assertSame('http://testing.dev/', Utils::url('/', true));
        $this->assertSame('http://testing.dev/path1', Utils::url('/path1', true));
        $this->assertSame('http://testing.dev/path1/path2', Utils::url('/path1/path2', true));
        $this->assertSame('http://testing.dev/random/path1/path2', Utils::url('/random/path1/path2', true));
        $this->assertSame('http://testing.dev/foobar.jpg', Utils::url('/foobar.jpg', true));
        $this->assertSame('http://testing.dev/path1/foobar.jpg', Utils::url('/path1/foobar.jpg', true));
        $this->assertSame('http://testing.dev/path1/path2/foobar.jpg', Utils::url('/path1/path2/foobar.jpg', true));
        $this->assertSame('http://testing.dev/random/path1/path2/foobar.jpg', Utils::url('/random/path1/path2/foobar.jpg', true));

        // Relative paths from Grav root.
        $this->assertSame('/subdir', Utils::url('subdir'));
        $this->assertSame('/subdir/path1', Utils::url('subdir/path1'));
        $this->assertSame('/subdir/path1/path2', Utils::url('subdir/path1/path2'));
        $this->assertSame('/path1', Utils::url('path1'));
        $this->assertSame('/path1/path2', Utils::url('path1/path2'));
        $this->assertSame('/foobar.jpg', Utils::url('foobar.jpg'));
        $this->assertSame('http://testing.dev/foobar.jpg', Utils::url('foobar.jpg', true));

        // Relative paths from Grav root with domain.
        $this->assertSame('http://testing.dev/foobar.jpg', Utils::url('foobar.jpg', true));
        $this->assertSame('http://testing.dev/foobar.jpg', Utils::url('/foobar.jpg', true));
        $this->assertSame('http://testing.dev/path1/foobar.jpg', Utils::url('/path1/foobar.jpg', true));

        // All Non-existing streams should be treated as external URI / protocol.
        $this->assertSame('http://domain.com/path', Utils::url('http://domain.com/path'));
        $this->assertSame('ftp://domain.com/path', Utils::url('ftp://domain.com/path'));
        $this->assertSame('sftp://domain.com/path', Utils::url('sftp://domain.com/path'));
        $this->assertSame('ssh://domain.com', Utils::url('ssh://domain.com'));
        $this->assertSame('pop://domain.com', Utils::url('pop://domain.com'));
        $this->assertSame('foo://bar/baz', Utils::url('foo://bar/baz'));
        $this->assertSame('foo://bar/baz', Utils::url('foo://bar/baz', true));
        // $this->assertSame('mailto:joe@domain.com', Utils::url('mailto:joe@domain.com', true)); // FIXME <-
    }

    public function testUrlWithRoot()
    {
        $this->uri->initializeWithUrlAndRootPath('http://testing.dev/subdir/path1/path2', '/subdir')->init();

        // Fail hard
        $this->assertSame(false, Utils::url('', true));
        $this->assertSame(false, Utils::url(''));
        $this->assertSame(false, Utils::url(new stdClass()));
        $this->assertSame(false, Utils::url(['foo','bar','baz']));
        $this->assertSame(false, Utils::url('user://does/not/exist'));

        // Fail Gracefully
        $this->assertSame('/subdir/', Utils::url('/', false, true));
        $this->assertSame('/subdir/', Utils::url('', false, true));
        $this->assertSame('/subdir/', Utils::url(new stdClass(), false, true));
        $this->assertSame('/subdir/', Utils::url(['foo','bar','baz'], false, true));
        $this->assertSame('/subdir/user/does/not/exist', Utils::url('user://does/not/exist', false, true));

        // Simple paths
        $this->assertSame('/subdir/', Utils::url('/'));
        $this->assertSame('/subdir/path1', Utils::url('/path1'));
        $this->assertSame('/subdir/path1/path2', Utils::url('/path1/path2'));
        $this->assertSame('/subdir/random/path1/path2', Utils::url('/random/path1/path2'));
        $this->assertSame('/subdir/foobar.jpg', Utils::url('/foobar.jpg'));
        $this->assertSame('/subdir/path1/foobar.jpg', Utils::url('/path1/foobar.jpg'));
        $this->assertSame('/subdir/path1/path2/foobar.jpg', Utils::url('/path1/path2/foobar.jpg'));
        $this->assertSame('/subdir/random/path1/path2/foobar.jpg', Utils::url('/random/path1/path2/foobar.jpg'));

        // Simple paths with domain
        $this->assertSame('http://testing.dev/subdir/', Utils::url('/', true));
        $this->assertSame('http://testing.dev/subdir/path1', Utils::url('/path1', true));
        $this->assertSame('http://testing.dev/subdir/path1/path2', Utils::url('/path1/path2', true));
        $this->assertSame('http://testing.dev/subdir/random/path1/path2', Utils::url('/random/path1/path2', true));
        $this->assertSame('http://testing.dev/subdir/foobar.jpg', Utils::url('/foobar.jpg', true));
        $this->assertSame('http://testing.dev/subdir/path1/foobar.jpg', Utils::url('/path1/foobar.jpg', true));
        $this->assertSame('http://testing.dev/subdir/path1/path2/foobar.jpg', Utils::url('/path1/path2/foobar.jpg', true));
        $this->assertSame('http://testing.dev/subdir/random/path1/path2/foobar.jpg', Utils::url('/random/path1/path2/foobar.jpg', true));

        // Paths including the grav base.
        $this->assertSame('/subdir/', Utils::url('/subdir'));
        $this->assertSame('/subdir/path1', Utils::url('/subdir/path1'));
        $this->assertSame('/subdir/path1/path2', Utils::url('/subdir/path1/path2'));
        $this->assertSame('/subdir/foobar.jpg', Utils::url('/subdir/foobar.jpg'));
        $this->assertSame('/subdir/path1/foobar.jpg', Utils::url('/subdir/path1/foobar.jpg'));

        // Relative paths from Grav root with domain.
        $this->assertSame('http://testing.dev/subdir/', Utils::url('/subdir', true));
        $this->assertSame('http://testing.dev/subdir/path1', Utils::url('/subdir/path1', true));
        $this->assertSame('http://testing.dev/subdir/path1/path2', Utils::url('/subdir/path1/path2', true));
        $this->assertSame('http://testing.dev/subdir/foobar.jpg', Utils::url('/subdir/foobar.jpg', true));
        $this->assertSame('http://testing.dev/subdir/path1/foobar.jpg', Utils::url('/subdir/path1/foobar.jpg', true));

        // Relative paths from Grav root.
        $this->assertSame('/subdir/subdir', Utils::url('subdir'));
        $this->assertSame('/subdir/subdir/path1', Utils::url('subdir/path1'));
        $this->assertSame('/subdir/subdir/path1/path2', Utils::url('subdir/path1/path2'));
        $this->assertSame('/subdir/path1', Utils::url('path1'));
        $this->assertSame('/subdir/path1/path2', Utils::url('path1/path2'));
        $this->assertSame('/subdir/foobar.jpg', Utils::url('foobar.jpg'));
        $this->assertSame('http://testing.dev/subdir/foobar.jpg', Utils::url('foobar.jpg', true));

        // All Non-existing streams should be treated as external URI / protocol.
        $this->assertSame('http://domain.com/path', Utils::url('http://domain.com/path'));
        $this->assertSame('ftp://domain.com/path', Utils::url('ftp://domain.com/path'));
        $this->assertSame('sftp://domain.com/path', Utils::url('sftp://domain.com/path'));
        $this->assertSame('ssh://domain.com', Utils::url('ssh://domain.com'));
        $this->assertSame('pop://domain.com', Utils::url('pop://domain.com'));
        $this->assertSame('foo://bar/baz', Utils::url('foo://bar/baz'));
        $this->assertSame('foo://bar/baz', Utils::url('foo://bar/baz', true));
        // $this->assertSame('mailto:joe@domain.com', Utils::url('mailto:joe@domain.com', true)); // FIXME <-
    }

    public function testUrlWithStreams()
    {
    }

    public function testUrlwithExternals()
    {
        $this->uri->initializeWithUrl('http://testing.dev/path1/path2')->init();
        $this->assertSame('http://foo.com', Utils::url('http://foo.com'));
        $this->assertSame('https://foo.com', Utils::url('https://foo.com'));
        $this->assertSame('//foo.com', Utils::url('//foo.com'));
        $this->assertSame('//foo.com?param=x', Utils::url('//foo.com?param=x'));
    }

    public function testCheckFilename()
    {
        // configure extension for consistent results
        /** @var \Grav\Common\Config\Config $config */
        $config = $this->grav['config'];
        $config->set('security.uploads_dangerous_extensions', ['php', 'html', 'htm', 'exe', 'js']);

        $this->assertFalse(Utils::checkFilename('foo.php'));
        $this->assertFalse(Utils::checkFilename('bar.js'));

        $this->assertTrue(Utils::checkFilename('foo.json'));
        $this->assertTrue(Utils::checkFilename('foo.xml'));
        $this->assertTrue(Utils::checkFilename('foo.yaml'));
        $this->assertTrue(Utils::checkFilename('foo.yml'));
    }
}
