<?php

/**
 * @author  Sebastian Weinert <swe@neos-it.de>
 * @access private
 */
class Ut_NextADInt_Adi_User_Persistence_RepositoryTest extends Ut_BasicTest
{
	/** @var NextADInt_Core_Util_ExceptionUtil|\Mockery\MockInterface */
	private $exceptionUtil;

	public function setUp()
	{
		parent::setUp();

		$this->exceptionUtil = $this->createUtilClassMock('NextADInt_Core_Util_ExceptionUtil');
	}

	public function tearDown()
	{
		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function findById_delegatesCallToInternalMethod()
	{
		$sut = $this->sut(array('findByKey'));

		$expected = $this->createMock('WP_User');

		$sut->expects($this->once())
			->method('findByKey')
			->with('id', 1)
			->willReturn($expected);

		$actual = $sut->findById(1);

		$this->assertEquals($expected, $actual);
	}

	/**
	 * @test
	 */
	public function findByUsername_delegatesCallToInternalMethod()
	{
		$sut = $this->sut(array('findByKey'));

		$expected = $this->createMock('WP_User');

		$sut->expects($this->once())
			->method('findByKey')
			->with('login', 'test')
			->willReturn($expected);

		$actual = $sut->findByUsername('test');

		$this->assertEquals($expected, $actual);
	}

	/**
	 * @test
	 */
	public function findByEmail_delegatesCallToInternalMethod()
	{
		$sut = $this->sut(array('findByKey'));

		$expected = $this->createMock('WP_User');

		$sut->expects($this->once())
			->method('findByKey')
			->with('email', 'test@test.com')
			->willReturn($expected);

		$actual = $sut->findByEmail('test@test.com');

		$this->assertEquals($expected, $actual);
	}

	/**
	 * @test
	 */
	public function findByKey_delegatesCallToWordPressFunction()
	{
		$sut = $this->sut(array());

		$expected = $this->createMock('WP_User');

		WP_Mock::wpFunction('get_user_by', array(
			'args'   => array('login', 'test'),
			'times'  => 1,
			'return' => $expected,
		));

		$actual = $this->invokeMethod($sut, 'findByKey', array('login', 'test'));

		$this->assertEquals($expected, $actual);
	}

	/**
	 * @test
	 */
	public function findByMetaKey_delegatesCallToWordPressFunction()
	{
		$sut = $this->sut();

		$expected = array($expected = $this->createMock('WP_User'));

		WP_Mock::wpFunction('get_users', array(
			'args'   => array(array('meta_key' => 'key', 'meta_value' => 'value', 'fields' => 'all')),
			'times'  => 1,
			'return' => $expected,
		));

		$actual = $this->invokeMethod($sut, 'findByMetaKey', array('key', 'value'));
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @test
	 */
	public function findByMetaKey_itIgnoresMetaValue_ifValueIsNull()
	{
		$sut = $this->sut();

		$expected = array($expected = $this->createMock('WP_User'));

		WP_Mock::wpFunction('get_users', array(
			'args'   => array(array('meta_key' => 'key', 'fields' => 'all')),
			'times'  => 1,
			'return' => $expected,
		));

		$actual = $this->invokeMethod($sut, 'findByMetaKey', array('key'));
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @test
	 */
	public function findUserMeta_delegatesCallToWordPressFunction()
	{
		$sut = $this->sut();

		$expected = array('first_name' => array('My first name'));

		WP_Mock::wpFunction('get_user_meta', array(
			'args'   => array(666),
			'times'  => 1,
			'return' => $expected,
		));

		$actual = $sut->findUserMeta(666);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @test
	 */
	public function updateMetaKey_delegatesCallToWordPressFunction()
	{
		$sut = $this->sut();

		WP_Mock::wpFunction('update_user_meta', array(
			'args' => array(1, 'key', 'value'),
		));

		$sut->updateMetaKey(1, 'key', 'value');
	}

	/**
	 * @test
	 */
	public function findBySAMAccountName_withoutUser_returnsFalse()
	{
		$sut = $this->sut(array('findByMetaKey'));

		$actual = $sut->findBySAMAccountName('sam');

		$this->assertFalse($actual);
	}

	/**
	 * @test
	 */
	public function findBySAMAccountName_withUser_returnsUser()
	{
		$sut = $this->sut(array('findByMetaKey'));

		$wpUser = $this->createMock('WP_User');

		$sut->expects($this->once())
			->method('findByMetaKey')
			->with('adi2_samaccountname', 'sam')
			->willReturn(array($wpUser));

		$actual = $sut->findBySAMAccountName('sam');

		$this->assertEquals($wpUser, $actual);
	}

	/**
	 * @test
	 */
	public function updateSAMAccountName_delegatesCallToInternalMethod()
	{
		$sut = $this->sut(array('updateMetaKey'));

		$sut->expects($this->once())
			->method('updateMetaKey')
			->with(1, 'adi2_samaccountname', 'sam');

		$sut->updateSAMAccountName(1, 'sam');
	}

	/**
	 * @test
	 */
	public function findByObjectGuid_withoutUser_returnsFalse()
	{
		$sut = $this->sut(array('findByMetaKey'));

		$actual = $sut->findByObjectGuid('guid1');

		$this->assertFalse($actual);
	}

	/**
	 * @test
	 */
	public function findByObjectGuid_withUser_returnsUser()
	{
		$sut = $this->sut(array('findByMetaKey'));

		$wpUser = $this->createMock('WP_User');

		$sut->expects($this->once())
			->method('findByMetaKey')
			->with('adi2_objectguid', 'guid')
			->willReturn(array($wpUser));

		$actual = $sut->findByObjectGuid('guid');

		$this->assertEquals($wpUser, $actual);
	}

	/**
	 * @test
	 */
	public function isEmailExisting_delegatesCallToFindByEmailMethod()
	{
		$sut = $this->sut(array('findByEmail'));

		$sut->expects($this->once())
			->method('findByEmail')
			->with('test@test.com');

		$sut->isEmailExisting('test@test.com');
	}

	/**
	 * @test
	 */
	public function isEmailExisting_returnsTrueIfUserIsExisting()
	{
		$sut = $this->sut(array('findByEmail'));

		$wpUser = $this->createMock('WP_User');

		$sut->expects($this->once())
			->method('findByEmail')
			->with('test@test.com')
			->willReturn($wpUser);

		$actual = $sut->isEmailExisting('test@test.com');

		$this->assertTrue($actual);
	}

	/**
	 * @test
	 */
	public function isEmailExisting_returnsFalseIfUserIsNotExisting()
	{
		$sut = $this->sut(array('findByEmail'));

		$sut->expects($this->once())
			->method('findByEmail')
			->with('test@test.com')
			->willReturn(false);

		$actual = $sut->isEmailExisting('test@test.com');

		$this->assertFalse($actual);
	}

	/**
	 * @test
	 */
	public function updateEmail_delegatesToInternalUpdatePropertyMethod()
	{
		$sut = $this->sut(array('updateProperty'));

		$sut->expects($this->once())
			->method('updateProperty')
			->with(1, 'user_email', 'test@test.com');

		$sut->updateEmail(1, 'test@test.com');
	}

	/**
	 * @test
	 */
	public function updatePassword_delegatesToInternalUpdatePropertyMethod()
	{
		$sut = $this->sut(array('updateProperty'));

		$sut->expects($this->once())
			->method('updateProperty')
			->with(1, 'user_pass', 'password');

		$sut->updatePassword(1, 'password');
	}

	/**
	 * @test
	 */
	public function updateProperty_doesNotTriggerWordPressErrorPart()
	{
		$sut = $this->sut(array('findById'));

		WP_Mock::wpFunction('wp_update_user', array(
			'args'   => array(
				array('ID' => 1, 'user_email' => 'test@test.com'),
			),
			'times'  => 1,
			'return' => 1,
		));

		WP_Mock::wpFunction('is_wp_error', array(
			'args'   => 1,
			'times'  => 1,
			'return' => false,
		));

		$sut->expects($this->never())
			->method('findById')
			->with(1);

		$this->invokeMethod($sut, 'updateProperty', array(1, 'user_email', 'test@test.com'));
	}

	/**
	 * @test
	 */
	public function updateProperty_doesTriggerWordPressErrorPart()
	{
		$sut = $this->sut(array('findById'));

		$wpUser = $this->createMock('WP_User');
		$wpUser->display_name = 'display_name';

		$wpErrorMock = $this->createMockedObject('WP_Error', array(), array('get_error_messages'));
		$wpErrorMock->expects($this->once())
			->method('get_error_messages')
			->willReturn(array());

		WP_Mock::wpFunction('wp_update_user', array(
			'args'   => array(
				array('ID' => 1, 'user_email' => 'test@test.com'),
			),
			'times'  => 1,
			'return' => $wpErrorMock,
		));

		WP_Mock::wpFunction('is_wp_error', array(
			'args'   => array($wpErrorMock),
			'times'  => 1,
			'return' => true,
		));

		$sut->expects($this->once())
			->method('findById')
			->with(1)
			->willReturn($wpUser);

		$this->invokeMethod($sut, 'updateProperty', array(1, 'user_email', 'test@test.com'));
	}

	/**
	 * @test
	 */
	public function create_withErrorOnCreation_throwsException()
	{
		$sut = $this->sut(null);

		$wpError = $this->createMockedObject('WP_Error', array(), array('get_error_messages'));

		$adiUser = $this->createMock('NextADInt_Adi_User');

		$this->behave($adiUser, 'getUserLogin', 'username');
		$this->behave($adiUser, 'getCredentials', new NextADInt_Adi_Authentication_Credentials('username', 'password'));

		WP_Mock::wpFunction('is_wp_error', array(
			'args'   => array($wpError),
			'times'  => 1,
			'return' => true,
		));

		WP_Mock::wpFunction('wp_create_user', array(
			'args'   => array('username', 'password'),
			'times'  => 1,
			'return' => $wpError,
		));

		$this->exceptionUtil->shouldReceive('handleWordPressErrorAsException')
			->once();

		$sut->create($adiUser);
	}

	/**
	 * @test
	 */
	public function create_itReturnsResult()
	{
		$sut = $this->sut(null);

		$adiUser = $this->createMock('NextADInt_Adi_User');

		$this->behave($adiUser, 'getUserLogin', 'username');
		$this->behave($adiUser, 'getCredentials', new NextADInt_Adi_Authentication_Credentials('username', 'password'));

		WP_Mock::wpFunction('wp_create_user', array(
			'args'   => array('username', 'password'),
			'times'  => 1,
			'return' => 1,
		));

		WP_Mock::wpFunction('is_wp_error', array(
			'args'   => 1,
			'times'  => 1,
			'return' => false,
		));

		$this->exceptionUtil->shouldReceive('handleWordPressErrorAsException')
			->never();

		$actual = $sut->create($adiUser);
		$this->assertEquals(1, $actual);
	}

	/**
	 * @test
	 */
	public function update_itReturnswithErrorOnUpdate_returnsErrorObject()
	{
		$sut = $this->sut(null);

		$wpError = $this->createMockedObject('WP_Error', array(), array('get_error_messages'));
		$wpError->expects($this->once())
			->method('get_error_messages')
			->willReturn(array());

		$adiUser = $this->createMock('NextADInt_Adi_User');

		$adiUser->expects($this->once())
			->method('getUserLogin')
			->willReturn('username');

		$adiUser->expects($this->once())
			->method('getId')
			->willReturn(1);

		WP_Mock::wpFunction('is_wp_error', array(
			'args'   => array($wpError),
			'times'  => 1,
			'return' => true,
		));

		WP_Mock::wpFunction('wp_update_user', array(
			'args'   => array(array()),
			'times'  => 1,
			'return' => $wpError,
		));

		$actual = $sut->update($adiUser, array());
		$this->assertEquals($wpError, $actual);
	}

	/**
	 * Create a partial mock for our {@see NextADInt_Adi_User_Persistence_Repository}.
	 *
	 * @param $methods
	 *
	 * @return NextADInt_Adi_User_Persistence_Repository|PHPUnit_Framework_MockObject_MockObject
	 */
	private function sut($methods = null)
	{
		return $this->getMockBuilder('NextADInt_Adi_User_Persistence_Repository')
			->setConstructorArgs(array())
			->setMethods($methods)
			->getMock();
	}
}