<?php
// source: phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/conf/config.neon
// source: phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/conf/config.level5.neon
// source: /home/runner/work/snck-panel/snck-panel/laravel-panel/phpstan.neon
// source: array

/** @noinspection PhpParamsInspection,PhpMethodMayBeStaticInspection */

declare(strict_types=1);

class Container_93320a03c5 extends _PHPStan_034afd3ee\Nette\DI\Container
{
	protected $tags = [
		'phpstan.stubFilesExtension' => [
			'014' => true,
			'015' => true,
			'016' => true,
			'022' => true,
			'024' => true,
			'028' => true,
			'01025' => true,
		],
		'phpstan.diagnoseExtension' => ['038' => true, '039' => true, '040' => true, '082' => true, '0108' => true],
		'phpstan.broker.allowedSubTypesClassReflectionExtension' => ['044' => true, '046' => true],
		'phpstan.broker.dynamicMethodReturnTypeExtension' => [
			'069' => true,
			'0350' => true,
			'0361' => true,
			'0374' => true,
			'0382' => true,
			'0390' => true,
			'0403' => true,
			'0409' => true,
			'0455' => true,
			'0475' => true,
			'0490' => true,
			'0519' => true,
			'0560' => true,
			'0897' => true,
			'0898' => true,
			'0899' => true,
			'0900' => true,
			'0901' => true,
			'0902' => true,
			'0903' => true,
			'0904' => true,
			'0905' => true,
			'0906' => true,
			'0907' => true,
			'0952' => true,
			'0953' => true,
			'0954' => true,
			'0955' => true,
			'0956' => true,
			'0958' => true,
			'0959' => true,
			'0965' => true,
			'0967' => true,
			'0968' => true,
			'0969' => true,
			'0970' => true,
			'0971' => true,
			'0972' => true,
			'0973' => true,
			'0981' => true,
			'0982' => true,
			'0983' => true,
			'01004' => true,
			'01005' => true,
			'01042' => true,
			'01043' => true,
			'01044' => true,
			'01045' => true,
			'01046' => true,
			'01047' => true,
			'01048' => true,
			'01062' => true,
			'01063' => true,
		],
		'phpstan.parser.richParserNodeVisitor' => [
			'083' => true,
			'084' => true,
			'085' => true,
			'086' => true,
			'087' => true,
			'088' => true,
			'089' => true,
			'091' => true,
			'092' => true,
			'093' => true,
			'094' => true,
			'095' => true,
			'096' => true,
			'097' => true,
			'098' => true,
			'099' => true,
			'0100' => true,
			'0101' => true,
			'0102' => true,
			'0103' => true,
			'0104' => true,
			'0105' => true,
			'0106' => true,
			'0107' => true,
		],
		'phpstan.rules.rule' => [
			'0135' => true,
			'0136' => true,
			'0137' => true,
			'0138' => true,
			'0139' => true,
			'0157' => true,
			'0158' => true,
			'0159' => true,
			'0160' => true,
			'0161' => true,
			'0162' => true,
			'0163' => true,
			'0164' => true,
			'0165' => true,
			'0166' => true,
			'0572' => true,
			'0573' => true,
			'0574' => true,
			'0575' => true,
			'0576' => true,
			'0577' => true,
			'0578' => true,
			'0579' => true,
			'0580' => true,
			'0581' => true,
			'0582' => true,
			'0583' => true,
			'0584' => true,
			'0585' => true,
			'0586' => true,
			'0587' => true,
			'0588' => true,
			'0589' => true,
			'0590' => true,
			'0591' => true,
			'0592' => true,
			'0593' => true,
			'0594' => true,
			'0595' => true,
			'0596' => true,
			'0597' => true,
			'0598' => true,
			'0599' => true,
			'0600' => true,
			'0601' => true,
			'0602' => true,
			'0603' => true,
			'0604' => true,
			'0605' => true,
			'0606' => true,
			'0607' => true,
			'0608' => true,
			'0609' => true,
			'0610' => true,
			'0611' => true,
			'0612' => true,
			'0613' => true,
			'0614' => true,
			'0615' => true,
			'0616' => true,
			'0617' => true,
			'0618' => true,
			'0619' => true,
			'0620' => true,
			'0621' => true,
			'0622' => true,
			'0623' => true,
			'0624' => true,
			'0625' => true,
			'0626' => true,
			'0627' => true,
			'0628' => true,
			'0629' => true,
			'0630' => true,
			'0631' => true,
			'0632' => true,
			'0633' => true,
			'0634' => true,
			'0635' => true,
			'0636' => true,
			'0637' => true,
			'0638' => true,
			'0639' => true,
			'0640' => true,
			'0641' => true,
			'0642' => true,
			'0643' => true,
			'0644' => true,
			'0645' => true,
			'0646' => true,
			'0647' => true,
			'0648' => true,
			'0649' => true,
			'0650' => true,
			'0651' => true,
			'0652' => true,
			'0653' => true,
			'0654' => true,
			'0655' => true,
			'0656' => true,
			'0657' => true,
			'0658' => true,
			'0659' => true,
			'0660' => true,
			'0661' => true,
			'0662' => true,
			'0663' => true,
			'0664' => true,
			'0665' => true,
			'0666' => true,
			'0667' => true,
			'0668' => true,
			'0669' => true,
			'0670' => true,
			'0671' => true,
			'0672' => true,
			'0673' => true,
			'0674' => true,
			'0675' => true,
			'0676' => true,
			'0677' => true,
			'0678' => true,
			'0679' => true,
			'0680' => true,
			'0681' => true,
			'0682' => true,
			'0683' => true,
			'0684' => true,
			'0685' => true,
			'0686' => true,
			'0687' => true,
			'0688' => true,
			'0689' => true,
			'0690' => true,
			'0691' => true,
			'0692' => true,
			'0693' => true,
			'0694' => true,
			'0695' => true,
			'0696' => true,
			'0697' => true,
			'0698' => true,
			'0699' => true,
			'0700' => true,
			'0701' => true,
			'0702' => true,
			'0703' => true,
			'0704' => true,
			'0705' => true,
			'0706' => true,
			'0707' => true,
			'0708' => true,
			'0709' => true,
			'0710' => true,
			'0711' => true,
			'0712' => true,
			'0713' => true,
			'0714' => true,
			'0715' => true,
			'0716' => true,
			'0717' => true,
			'0718' => true,
			'0719' => true,
			'0720' => true,
			'0721' => true,
			'0722' => true,
			'0723' => true,
			'0724' => true,
			'0725' => true,
			'0726' => true,
			'0727' => true,
			'0728' => true,
			'0729' => true,
			'0730' => true,
			'0731' => true,
			'0732' => true,
			'0733' => true,
			'0734' => true,
			'0735' => true,
			'0736' => true,
			'0737' => true,
			'0738' => true,
			'0739' => true,
			'0740' => true,
			'0741' => true,
			'0742' => true,
			'0743' => true,
			'0744' => true,
			'0745' => true,
			'0746' => true,
			'0747' => true,
			'0748' => true,
			'0749' => true,
			'0750' => true,
			'0751' => true,
			'0752' => true,
			'0753' => true,
			'0754' => true,
			'0755' => true,
			'0756' => true,
			'0757' => true,
			'0758' => true,
			'0759' => true,
			'0760' => true,
			'0761' => true,
			'0762' => true,
			'0763' => true,
			'0764' => true,
			'0765' => true,
			'0766' => true,
			'0767' => true,
			'0768' => true,
			'0769' => true,
			'0770' => true,
			'0771' => true,
			'0772' => true,
			'0773' => true,
			'0774' => true,
			'0775' => true,
			'0776' => true,
			'0777' => true,
			'0778' => true,
			'0779' => true,
			'0780' => true,
			'0781' => true,
			'0782' => true,
			'0783' => true,
			'0784' => true,
			'0785' => true,
			'0786' => true,
			'0787' => true,
			'0788' => true,
			'0789' => true,
			'0790' => true,
			'0791' => true,
			'0792' => true,
			'0793' => true,
			'0794' => true,
			'0795' => true,
			'0796' => true,
			'0797' => true,
			'0798' => true,
			'0799' => true,
			'0800' => true,
			'0801' => true,
			'0802' => true,
			'0803' => true,
			'0804' => true,
			'0805' => true,
			'0806' => true,
			'0807' => true,
			'0808' => true,
			'0809' => true,
			'0810' => true,
			'0811' => true,
			'0812' => true,
			'0813' => true,
			'0814' => true,
			'0815' => true,
			'0816' => true,
			'0817' => true,
			'0818' => true,
			'0819' => true,
			'0820' => true,
			'0821' => true,
			'0822' => true,
			'0823' => true,
			'0824' => true,
			'0825' => true,
			'0826' => true,
			'0827' => true,
			'0828' => true,
			'0829' => true,
			'0830' => true,
			'0831' => true,
			'0832' => true,
			'0833' => true,
			'0834' => true,
			'0835' => true,
			'0836' => true,
			'0837' => true,
			'0838' => true,
			'0839' => true,
			'0840' => true,
			'0841' => true,
			'0842' => true,
			'0843' => true,
			'0844' => true,
			'0845' => true,
			'0846' => true,
			'0847' => true,
			'0848' => true,
			'0849' => true,
			'0850' => true,
			'0851' => true,
			'0852' => true,
			'0853' => true,
			'0854' => true,
			'0855' => true,
			'0856' => true,
			'0857' => true,
			'0858' => true,
			'0859' => true,
			'0860' => true,
			'0861' => true,
			'0862' => true,
			'0863' => true,
			'0864' => true,
			'0865' => true,
			'0866' => true,
			'0867' => true,
			'0868' => true,
			'0869' => true,
			'0870' => true,
			'0871' => true,
			'0872' => true,
			'0873' => true,
			'0874' => true,
			'0875' => true,
			'0876' => true,
			'0877' => true,
			'0922' => true,
			'0923' => true,
			'0924' => true,
			'0996' => true,
			'0997' => true,
			'0999' => true,
			'01001' => true,
			'01019' => true,
			'01021' => true,
			'01022' => true,
			'01023' => true,
			'rules.0' => true,
			'rules.1' => true,
			'rules.2' => true,
			'rules.3' => true,
		],
		'phpstan.exprHandler' => [
			'0196' => true,
			'0197' => true,
			'0198' => true,
			'0199' => true,
			'0200' => true,
			'0201' => true,
			'0202' => true,
			'0203' => true,
			'0204' => true,
			'0205' => true,
			'0206' => true,
			'0207' => true,
			'0208' => true,
			'0209' => true,
			'0210' => true,
			'0211' => true,
			'0212' => true,
			'0213' => true,
			'0214' => true,
			'0215' => true,
			'0216' => true,
			'0217' => true,
			'0218' => true,
			'0219' => true,
			'0220' => true,
			'0221' => true,
			'0222' => true,
			'0223' => true,
			'0224' => true,
			'0225' => true,
			'0226' => true,
			'0227' => true,
			'0228' => true,
			'0229' => true,
			'0230' => true,
			'0231' => true,
			'0232' => true,
			'0233' => true,
			'0234' => true,
			'0235' => true,
			'0236' => true,
			'0237' => true,
			'0238' => true,
			'0239' => true,
			'0240' => true,
			'0241' => true,
			'0242' => true,
			'0243' => true,
			'0244' => true,
			'0245' => true,
			'0246' => true,
			'0247' => true,
			'0248' => true,
			'0249' => true,
			'0250' => true,
			'0251' => true,
			'0252' => true,
			'0253' => true,
			'0254' => true,
			'0255' => true,
			'0256' => true,
		],
		'phpstan.perFileAnalysisResettable' => ['0213' => true, '0245' => true, '0264' => true, '0265' => true],
		'phpstan.stmtHandler' => [
			'0283' => true,
			'0284' => true,
			'0285' => true,
			'0286' => true,
			'0287' => true,
			'0288' => true,
			'0289' => true,
			'0290' => true,
			'0291' => true,
			'0292' => true,
			'0293' => true,
			'0294' => true,
			'0295' => true,
			'0296' => true,
			'0297' => true,
			'0298' => true,
			'0299' => true,
			'0300' => true,
			'0301' => true,
			'0302' => true,
			'0303' => true,
			'0304' => true,
			'0305' => true,
			'0306' => true,
			'0307' => true,
			'0308' => true,
			'0309' => true,
			'0310' => true,
			'0311' => true,
			'0312' => true,
			'0313' => true,
			'0314' => true,
		],
		'phpstan.resultCacheValueExtension' => ['0322' => true, '0324' => true, '0325' => true, '0375' => true],
		'phpstan.broker.dynamicFunctionReturnTypeExtension' => [
			'0347' => true,
			'0351' => true,
			'0355' => true,
			'0356' => true,
			'0357' => true,
			'0359' => true,
			'0360' => true,
			'0365' => true,
			'0367' => true,
			'0369' => true,
			'0372' => true,
			'0377' => true,
			'0378' => true,
			'0380' => true,
			'0383' => true,
			'0384' => true,
			'0385' => true,
			'0388' => true,
			'0389' => true,
			'0391' => true,
			'0392' => true,
			'0393' => true,
			'0395' => true,
			'0396' => true,
			'0397' => true,
			'0399' => true,
			'0401' => true,
			'0402' => true,
			'0410' => true,
			'0411' => true,
			'0413' => true,
			'0415' => true,
			'0419' => true,
			'0423' => true,
			'0425' => true,
			'0427' => true,
			'0428' => true,
			'0429' => true,
			'0430' => true,
			'0432' => true,
			'0433' => true,
			'0436' => true,
			'0437' => true,
			'0438' => true,
			'0440' => true,
			'0442' => true,
			'0444' => true,
			'0445' => true,
			'0446' => true,
			'0447' => true,
			'0448' => true,
			'0449' => true,
			'0457' => true,
			'0458' => true,
			'0459' => true,
			'0460' => true,
			'0461' => true,
			'0462' => true,
			'0463' => true,
			'0464' => true,
			'0466' => true,
			'0468' => true,
			'0471' => true,
			'0474' => true,
			'0477' => true,
			'0478' => true,
			'0479' => true,
			'0480' => true,
			'0482' => true,
			'0483' => true,
			'0485' => true,
			'0488' => true,
			'0489' => true,
			'0490' => true,
			'0491' => true,
			'0493' => true,
			'0494' => true,
			'0495' => true,
			'0498' => true,
			'0499' => true,
			'0502' => true,
			'0503' => true,
			'0504' => true,
			'0505' => true,
			'0508' => true,
			'0509' => true,
			'0510' => true,
			'0511' => true,
			'0512' => true,
			'0513' => true,
			'0521' => true,
			'0522' => true,
			'0524' => true,
			'0526' => true,
			'0529' => true,
			'0530' => true,
			'0531' => true,
			'0533' => true,
			'0535' => true,
			'0536' => true,
			'0537' => true,
			'0539' => true,
			'0542' => true,
			'0543' => true,
			'0544' => true,
			'0546' => true,
			'0547' => true,
			'0548' => true,
			'0549' => true,
			'0550' => true,
			'0551' => true,
			'0975' => true,
			'0976' => true,
			'0977' => true,
			'0978' => true,
			'0979' => true,
			'0980' => true,
			'0988' => true,
			'0989' => true,
			'0990' => true,
			'0991' => true,
			'01050' => true,
			'01051' => true,
		],
		'phpstan.dynamicFunctionThrowTypeExtension' => [
			'0348' => true,
			'0368' => true,
			'0394' => true,
			'0404' => true,
			'0406' => true,
			'0407' => true,
			'0421' => true,
			'0431' => true,
			'0434' => true,
			'0501' => true,
			'0506' => true,
			'0515' => true,
			'0517' => true,
			'0540' => true,
		],
		'phpstan.dynamicMethodThrowTypeExtension' => ['0349' => true, '0373' => true, '0379' => true, '0454' => true],
		'phpstan.typeSpecifier.functionTypeSpecifyingExtension' => [
			'0353' => true,
			'0358' => true,
			'0363' => true,
			'0364' => true,
			'0387' => true,
			'0400' => true,
			'0417' => true,
			'0422' => true,
			'0424' => true,
			'0426' => true,
			'0435' => true,
			'0453' => true,
			'0467' => true,
			'0469' => true,
			'0486' => true,
			'0514' => true,
			'0518' => true,
			'0525' => true,
			'0527' => true,
			'0528' => true,
			'0538' => true,
			'0984' => true,
			'0985' => true,
			'0986' => true,
			'0987' => true,
		],
		'phpstan.broker.dynamicStaticMethodReturnTypeExtension' => [
			'0354' => true,
			'0408' => true,
			'0409' => true,
			'0416' => true,
			'0441' => true,
			'0472' => true,
			'0473' => true,
			'0496' => true,
			'0507' => true,
			'0960' => true,
			'0961' => true,
			'0962' => true,
			'0963' => true,
			'0964' => true,
			'0966' => true,
			'0992' => true,
			'01006' => true,
			'01049' => true,
		],
		'phpstan.dynamicStaticMethodThrowTypeExtension' => [
			'0362' => true,
			'0381' => true,
			'0420' => true,
			'0451' => true,
			'0465' => true,
			'0497' => true,
			'0500' => true,
			'0516' => true,
			'0523' => true,
			'0532' => true,
		],
		'phpstan.typeSpecifier.methodTypeSpecifyingExtension' => ['0366' => true],
		'phpstan.broker.propertiesClassReflectionExtension' => [
			'0398' => true,
			'0949' => true,
			'0950' => true,
			'0951' => true,
			'0957' => true,
		],
		'phpstan.broker.operatorTypeSpecifyingExtension' => ['0412' => true, '0520' => true],
		'phpstan.functionParameterClosureTypeExtension' => [
			'0414' => true,
			'0443' => true,
			'0470' => true,
			'0487' => true,
			'0492' => true,
		],
		'phpstan.functionParameterOutTypeExtension' => ['0450' => true, '0452' => true, '0476' => true],
		'phpstan.broker.unaryOperatorTypeSpecifyingExtension' => ['0484' => true, '0541' => true],
		'phpstan.collector' => [
			'0878' => true,
			'0879' => true,
			'0880' => true,
			'0881' => true,
			'0882' => true,
			'0883' => true,
			'0884' => true,
			'0885' => true,
			'0886' => true,
			'01027' => true,
			'01028' => true,
			'01029' => true,
			'01030' => true,
			'01031' => true,
			'01032' => true,
			'01033' => true,
			'01038' => true,
			'01039' => true,
			'01040' => true,
		],
		'phpstan.broker.methodsClassReflectionExtension' => [
			'0935' => true,
			'0936' => true,
			'0937' => true,
			'0938' => true,
			'0939' => true,
			'0940' => true,
			'0941' => true,
			'0942' => true,
			'0943' => true,
			'0944' => true,
			'0945' => true,
			'0946' => true,
			'0947' => true,
			'0948' => true,
		],
		'phpstan.phpDoc.typeNodeResolverExtension' => [
			'0993' => true,
			'0994' => true,
			'01003' => true,
			'01007' => true,
			'01008' => true,
			'01010' => true,
			'01011' => true,
		],
		'phpstan.methodParameterClosureTypeExtension' => ['01009' => true],
		'phpstan.staticMethodParameterClosureTypeExtension' => ['01009' => true],
	];

	protected $types = ['container' => '_PHPStan_034afd3ee\Nette\DI\Container'];
	protected $aliases = [];

	protected $wiring = [
		'_PHPStan_034afd3ee\Nette\DI\Container' => [['container']],
		'PHPStan\Php\PhpVersionFactoryFactory' => [['01']],
		'PHPStan\Php\ConfiguredPhpVersionRangeHelper' => [['02']],
		'PHPStan\Php\PhpVersion' => [['03']],
		'PHPStan\Php\ComposerPhpVersionFactory' => [['04']],
		'PHPStan\Php\PhpVersionFactory' => [['05']],
		'PHPStan\File\FileMonitor' => [['06']],
		'PHPStan\File\FileContentHasher' => [['07']],
		'PHPStan\File\FileStatSignatures' => [['08']],
		'PHPStan\File\FileHelper' => [['09']],
		'PHPStan\File\RelativePathHelper' => [
			0 => ['relativePathHelper'],
			2 => [1 => 'simpleRelativePathHelper', 'parentDirectoryRelativePathHelper'],
		],
		'PHPStan\File\FuzzyRelativePathHelper' => [['relativePathHelper']],
		'PHPStan\File\IncludedFilePathResolver' => [['010']],
		'PHPStan\File\DirectoryWalker' => [['011']],
		'PHPStan\File\DeduplicatingFileReader' => [['012']],
		'PHPStan\File\FileExcluderFactory' => [['013']],
		'PHPStan\PhpDoc\StubFilesExtension' => [['014', '015', '016', '022', '024', '028', '01025']],
		'PHPStan\PhpDoc\ReflectionEnumStubFilesExtension' => [['014']],
		'PHPStan\PhpDoc\BcMathNumberStubFilesExtension' => [['015']],
		'PHPStan\PhpDoc\ReflectionClassStubFilesExtension' => [['016']],
		'PHPStan\PhpDoc\StubFilesProvider' => [['017']],
		'PHPStan\PhpDoc\DefaultStubFilesProvider' => [['017']],
		'PHPStan\PhpDoc\PhpDocInheritanceResolver' => [['018']],
		'PHPStan\PhpDoc\StubPhpDocProvider' => [['stubPhpDocProvider']],
		'PHPStan\PhpDoc\PhpDocStringResolver' => [['019']],
		'PHPStan\PhpDoc\ConstExprNodeResolver' => [['020']],
		'PHPStan\PhpDoc\PhpDocNodeResolver' => [['021']],
		'PHPStan\PhpDoc\JsonValidateStubFilesExtension' => [['022']],
		'PHPStan\PhpDoc\StubValidator' => [['023']],
		'PHPStan\PhpDoc\ExtDsStubFilesExtension' => [['024']],
		'PHPStan\PhpDoc\TypeStringResolver' => [['025']],
		'PHPStan\PhpDoc\TypeNodeResolver' => [['026']],
		'PHPStan\PhpDoc\TypeNodeResolverExtensionRegistryProvider' => [['027']],
		'PHPStan\PhpDoc\LazyTypeNodeResolverExtensionRegistryProvider' => [['027']],
		'PHPStan\PhpDoc\SocketSelectStubFilesExtension' => [['028']],
		'PHPStan\Cache\Cache' => [['029']],
		'PHPStan\Broker\AnonymousClassNameHelper' => [['030']],
		'PhpParser\NodeVisitorAbstract' => [
			[
				'031',
				'057',
				'083',
				'084',
				'085',
				'086',
				'087',
				'088',
				'089',
				'091',
				'092',
				'093',
				'094',
				'095',
				'096',
				'097',
				'098',
				'099',
				'0100',
				'0101',
				'0102',
				'0103',
				'0104',
				'0105',
				'0106',
				'0107',
				'0888',
			],
		],
		'PhpParser\NodeVisitor' => [
			[
				'031',
				'057',
				'083',
				'084',
				'085',
				'086',
				'087',
				'088',
				'089',
				'091',
				'092',
				'093',
				'094',
				'095',
				'096',
				'097',
				'098',
				'099',
				'0100',
				'0101',
				'0102',
				'0103',
				'0104',
				'0105',
				'0106',
				'0107',
				'0888',
			],
		],
		'PHPStan\Dependency\ExportedNodeVisitor' => [['031']],
		'PHPStan\Dependency\PackageDependencyResolver' => [['032']],
		'PHPStan\Dependency\DependencyResolver' => [['033']],
		'PHPStan\Dependency\ExportedNodeFetcher' => [['034']],
		'PHPStan\Dependency\ExportedNodeResolver' => [['035']],
		'PHPStan\Parallel\ParallelAnalyser' => [['036']],
		'PHPStan\Parallel\WorkerRunner' => [['037']],
		'PHPStan\Diagnose\DiagnoseExtension' => [['038', '039', '040', '082', '0108']],
		'PHPStan\Parallel\ForkParallelChecker' => [['038']],
		'PHPStan\Parallel\Scheduler' => [['039']],
		'PHPStan\Process\Restart\RestartDiagnoseExtension' => [['040']],
		'PHPStan\Process\CpuCoreCounter' => [['041']],
		'PHPStan\Reflection\PropertiesClassReflectionExtension' => [
			['042', '047', '051', '0398', '0949', '0950', '0951', '0957'],
		],
		'PHPStan\Reflection\Mixin\MixinPropertiesClassReflectionExtension' => [['042']],
		'PHPStan\Reflection\MethodsClassReflectionExtension' => [
			[
				'043',
				'048',
				'050',
				'071',
				'0935',
				'0936',
				'0937',
				'0938',
				'0939',
				'0940',
				'0941',
				'0942',
				'0943',
				'0944',
				'0945',
				'0946',
				'0947',
				'0948',
			],
		],
		'PHPStan\Reflection\Mixin\MixinMethodsClassReflectionExtension' => [['043']],
		'PHPStan\Reflection\AllowedSubTypesClassReflectionExtension' => [['044', '046']],
		'PHPStan\Reflection\Php\EnumAllowedSubTypesClassReflectionExtension' => [['044']],
		'PHPStan\Reflection\Php\PhpClassReflectionExtension' => [['045']],
		'PHPStan\Reflection\Php\SealedAllowedSubTypesClassReflectionExtension' => [['046']],
		'PHPStan\Reflection\Php\UniversalObjectCratesClassReflectionExtension' => [['047']],
		'PHPStan\Reflection\Php\Soap\SoapClientMethodsClassReflectionExtension' => [['048']],
		'PHPStan\Reflection\ConstructorsHelper' => [['049']],
		'PHPStan\Reflection\Annotations\AnnotationsMethodsClassReflectionExtension' => [['050']],
		'PHPStan\Reflection\Annotations\AnnotationsPropertiesClassReflectionExtension' => [['051']],
		'PHPStan\Reflection\AttributeReflectionFactory' => [['052']],
		'PHPStan\Reflection\ParameterAllowedConstantsMapProvider' => [['053']],
		'PHPStan\Reflection\ReflectionProvider\ReflectionProviderFactory' => [['reflectionProviderFactory']],
		'PHPStan\Reflection\ReflectionProvider\ReflectionProviderProvider' => [['054']],
		'PHPStan\Reflection\ReflectionProvider\LazyReflectionProviderProvider' => [['054']],
		'PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedDirectorySourceLocatorRepository' => [['055']],
		'PHPStan\Reflection\BetterReflection\SourceLocator\FileNodesFetcher' => [['056']],
		'PHPStan\Reflection\BetterReflection\SourceLocator\CachingVisitor' => [['057']],
		'PHPStan\Reflection\BetterReflection\SourceLocator\PreForkDirectorySymbolScanner' => [['058']],
		'PHPStan\Reflection\BetterReflection\SourceLocator\ComposerJsonAndInstalledJsonSourceLocatorMaker' => [['059']],
		'PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedDirectorySourceLocatorFactory' => [['060']],
		'PHPStan\Reflection\BetterReflection\SourceLocator\SymbolFinderInFiles' => [['061']],
		'PHPStan\Reflection\BetterReflection\SourceLocator\PhpFileCleaner' => [['062']],
		'PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedSingleFileSourceLocatorRepository' => [['063']],
		'PHPStan\Reflection\BetterReflection\SourceLocator\BootstrapAutoloaderExceptionReporter' => [['064']],
		'PHPStan\Reflection\BetterReflection\BetterReflectionSourceLocatorFactory' => [['065']],
		'PHPStan\Reflection\BetterReflection\SourceStubber\ExtensionVersionProvider' => [['066']],
		'PHPStan\Reflection\BetterReflection\SourceStubber\PhpStormStubsSourceStubberFactory' => [['067']],
		'PHPStan\Reflection\BetterReflection\SourceStubber\ReflectionSourceStubberFactory' => [['068']],
		'PHPStan\BetterReflection\Reflector\Reflector' => [['betterReflectionReflector']],
		'PHPStan\Reflection\BetterReflection\Reflector\MemoizingReflector' => [['betterReflectionReflector']],
		'PHPStan\Type\DynamicMethodReturnTypeExtension' => [
			[
				'069',
				'0350',
				'0361',
				'0374',
				'0382',
				'0390',
				'0403',
				'0409',
				'0455',
				'0475',
				'0490',
				'0519',
				'0560',
				'0897',
				'0898',
				'0899',
				'0900',
				'0901',
				'0902',
				'0903',
				'0904',
				'0905',
				'0906',
				'0907',
				'0952',
				'0953',
				'0954',
				'0955',
				'0956',
				'0958',
				'0959',
				'0965',
				'0967',
				'0968',
				'0969',
				'0970',
				'0971',
				'0972',
				'0973',
				'0981',
				'0982',
				'0983',
				'01004',
				'01005',
				'01042',
				'01043',
				'01044',
				'01045',
				'01046',
				'01047',
				'01048',
				'01057',
				'01062',
				'01063',
			],
		],
		'PHPStan\Reflection\BetterReflection\Type\AdapterReflectionEnumDynamicReturnTypeExtension' => [['069']],
		'PHPStan\Reflection\RequireExtension\RequireExtendsPropertiesClassReflectionExtension' => [['070']],
		'PHPStan\Reflection\RequireExtension\RequireExtendsMethodsClassReflectionExtension' => [['071']],
		'PHPStan\Reflection\InitializerExprTypeResolver' => [['072']],
		'PHPStan\Reflection\SignatureMap\SignatureMapProvider' => [['075'], ['073', '078']],
		'PHPStan\Reflection\SignatureMap\Php8SignatureMapProvider' => [['073']],
		'PHPStan\Reflection\SignatureMap\SignatureMapProviderFactory' => [['074']],
		'PHPStan\Reflection\SignatureMap\NativeFunctionReflectionProvider' => [['076']],
		'PHPStan\Reflection\SignatureMap\SignatureMapParser' => [['077']],
		'PHPStan\Reflection\SignatureMap\FunctionSignatureMapProvider' => [['078']],
		'PHPStan\Reflection\Deprecation\DeprecationProvider' => [['079']],
		'PhpParser\PrettyPrinter\Standard' => [1 => ['080']],
		'PhpParser\PrettyPrinterAbstract' => [1 => ['080']],
		'PhpParser\PrettyPrinter' => [1 => ['080']],
		'PHPStan\Node\Printer\Printer' => [['080']],
		'PHPStan\Node\Printer\ExprPrinter' => [['081']],
		'PHPStan\Turbo\TurboDiagnoseExtension' => [['082']],
		'PHPStan\Parser\StandaloneThrowExprVisitor' => [['083']],
		'PHPStan\Parser\ImplodeArgVisitor' => [['084']],
		'PHPStan\Parser\ArrayFilterArgVisitor' => [['085']],
		'PHPStan\Parser\ImmediatelyInvokedClosureVisitor' => [['086']],
		'PHPStan\Parser\CurlSetOptArrayArgVisitor' => [['087']],
		'PHPStan\Parser\TryCatchTypeVisitor' => [['088']],
		'PHPStan\Parser\ArrayWalkArgVisitor' => [['089']],
		'PHPStan\Parser\LexerFactory' => [['090']],
		'PHPStan\Parser\ParentStmtTypesVisitor' => [['091']],
		'PHPStan\Parser\NewAssignedToPropertyVisitor' => [['092']],
		'PHPStan\Parser\DeclarePositionVisitor' => [['093']],
		'PHPStan\Parser\ClosureBindToVarVisitor' => [['094']],
		'PHPStan\Parser\ArrayFindArgVisitor' => [['095']],
		'PHPStan\Parser\ArrayMapArgVisitor' => [['096']],
		'PHPStan\Parser\ClosureArgVisitor' => [['097']],
		'PHPStan\Parser\ArrayOffsetNormalizingVisitor' => [['098']],
		'PHPStan\Parser\GotoLabelVisitor' => [['099']],
		'PHPStan\Parser\TypeTraverserInstanceofVisitor' => [['0100']],
		'PHPStan\Parser\ClosureBindArgVisitor' => [['0101']],
		'PHPStan\Parser\UseAliasVisitor' => [['0102']],
		'PHPStan\Parser\LastConditionVisitor' => [['0103']],
		'PHPStan\Parser\MagicConstantParamDefaultVisitor' => [['0104']],
		'PHPStan\Parser\ArrowFunctionArgVisitor' => [['0105']],
		'PHPStan\Parser\AnonymousClassVisitor' => [['0106']],
		'PHPStan\Parser\CurlSetOptArgVisitor' => [['0107']],
		'PHPStan\Diagnose\SystemResourcesDiagnoseExtension' => [['0108']],
		'PHPStan\Rules\InternalTag\RestrictedInternalUsageHelper' => [['0109']],
		'PHPStan\Rules\ClassForbiddenNameCheck' => [['0110']],
		'PHPStan\Rules\PhpDoc\VarTagTypeRuleHelper' => [['0111']],
		'PHPStan\Rules\PhpDoc\IncompatiblePhpDocTypeCheck' => [['0112']],
		'PHPStan\Rules\PhpDoc\RequireExtendsCheck' => [['0113']],
		'PHPStan\Rules\PhpDoc\UnresolvableTypeHelper' => [['0114']],
		'PHPStan\Rules\PhpDoc\ConditionalReturnTypeRuleHelper' => [['0115']],
		'PHPStan\Rules\PhpDoc\AssertRuleHelper' => [['0116']],
		'PHPStan\Rules\PhpDoc\GenericCallableRuleHelper' => [['0117']],
		'PHPStan\Rules\RuleLevelHelper' => [['0118']],
		'PHPStan\Rules\Exceptions\ExceptionTypeResolver' => [1 => ['0119'], [1 => 'exceptionTypeResolver']],
		'PHPStan\Rules\Exceptions\DefaultExceptionTypeResolver' => [['0119']],
		'PHPStan\Rules\Exceptions\MissingCheckedExceptionInThrowsCheck' => [['0120']],
		'PHPStan\Rules\Exceptions\TooWideThrowTypeCheck' => [['0121']],
		'PHPStan\Rules\Arrays\NonexistentOffsetInArrayDimFetchCheck' => [['0122']],
		'PHPStan\Rules\NonStringableDynamicAccessCheck' => [['0123']],
		'PHPStan\Rules\ClassNameCheck' => [['0124']],
		'PHPStan\Rules\Variables\ParameterOutTypeCheck' => [['0125']],
		'PHPStan\Rules\NullsafeCheck' => [['0126']],
		'PHPStan\Rules\Registry' => [['registry']],
		'PHPStan\Rules\LazyRegistry' => [['registry']],
		'PHPStan\Rules\Comparison\ConstantConditionInTraitHelper' => [['0127']],
		'PHPStan\Rules\Comparison\ImpossibleCheckTypeHelper' => [['0128']],
		'PHPStan\Rules\Comparison\ConstantConditionRuleHelper' => [['0129']],
		'PHPStan\Rules\Comparison\FunctionCallConstantConditionHelper' => [['0130']],
		'PHPStan\Rules\Properties\PropertyDescriptor' => [['0131']],
		'PHPStan\Rules\Properties\AccessPropertiesCheck' => [['0132']],
		'PHPStan\Rules\Properties\PropertyReflectionFinder' => [['0133']],
		'PHPStan\Rules\Properties\AccessStaticPropertiesCheck' => [['0134']],
		'PHPStan\Rules\Rule' => [
			[
				'0135',
				'0136',
				'0137',
				'0138',
				'0139',
				'0157',
				'0158',
				'0159',
				'0160',
				'0161',
				'0162',
				'0163',
				'0164',
				'0165',
				'0166',
				'0173',
				'0908',
				'0909',
				'0910',
				'0911',
				'0912',
				'0913',
				'0917',
				'0920',
				'0921',
				'0922',
				'0923',
				'0924',
				'0925',
				'0926',
				'0927',
				'0928',
				'0929',
				'0930',
				'0931',
				'0932',
				'0933',
				'0934',
				'0995',
				'0996',
				'0997',
				'0998',
				'0999',
				'01000',
				'01001',
				'01002',
				'01019',
				'01021',
				'01022',
				'01023',
				'01026',
				'01037',
				'01064',
				'01065',
				'01066',
				'01067',
				'01068',
				'01069',
				'01070',
				'01071',
				'01072',
				'01073',
			],
			[
				'0572',
				'0573',
				'0574',
				'0575',
				'0576',
				'0577',
				'0578',
				'0579',
				'0580',
				'0581',
				'0582',
				'0583',
				'0584',
				'0585',
				'0586',
				'0587',
				'0588',
				'0589',
				'0590',
				'0591',
				'0592',
				'0593',
				'0594',
				'0595',
				'0596',
				'0597',
				'0598',
				'0599',
				'0600',
				'0601',
				'0602',
				'0603',
				'0604',
				'0605',
				'0606',
				'0607',
				'0608',
				'0609',
				'0610',
				'0611',
				'0612',
				'0613',
				'0614',
				'0615',
				'0616',
				'0617',
				'0618',
				'0619',
				'0620',
				'0621',
				'0622',
				'0623',
				'0624',
				'0625',
				'0626',
				'0627',
				'0628',
				'0629',
				'0630',
				'0631',
				'0632',
				'0633',
				'0634',
				'0635',
				'0636',
				'0637',
				'0638',
				'0639',
				'0640',
				'0641',
				'0642',
				'0643',
				'0644',
				'0645',
				'0646',
				'0647',
				'0648',
				'0649',
				'0650',
				'0651',
				'0652',
				'0653',
				'0654',
				'0655',
				'0656',
				'0657',
				'0658',
				'0659',
				'0660',
				'0661',
				'0662',
				'0663',
				'0664',
				'0665',
				'0666',
				'0667',
				'0668',
				'0669',
				'0670',
				'0671',
				'0672',
				'0673',
				'0674',
				'0675',
				'0676',
				'0677',
				'0678',
				'0679',
				'0680',
				'0681',
				'0682',
				'0683',
				'0684',
				'0685',
				'0686',
				'0687',
				'0688',
				'0689',
				'0690',
				'0691',
				'0692',
				'0693',
				'0694',
				'0695',
				'0696',
				'0697',
				'0698',
				'0699',
				'0700',
				'0701',
				'0702',
				'0703',
				'0704',
				'0705',
				'0706',
				'0707',
				'0708',
				'0709',
				'0710',
				'0711',
				'0712',
				'0713',
				'0714',
				'0715',
				'0716',
				'0717',
				'0718',
				'0719',
				'0720',
				'0721',
				'0722',
				'0723',
				'0724',
				'0725',
				'0726',
				'0727',
				'0728',
				'0729',
				'0730',
				'0731',
				'0732',
				'0733',
				'0734',
				'0735',
				'0736',
				'0737',
				'0738',
				'0739',
				'0740',
				'0741',
				'0742',
				'0743',
				'0744',
				'0745',
				'0746',
				'0747',
				'0748',
				'0749',
				'0750',
				'0751',
				'0752',
				'0753',
				'0754',
				'0755',
				'0756',
				'0757',
				'0758',
				'0759',
				'0760',
				'0761',
				'0762',
				'0763',
				'0764',
				'0765',
				'0766',
				'0767',
				'0768',
				'0769',
				'0770',
				'0771',
				'0772',
				'0773',
				'0774',
				'0775',
				'0776',
				'0777',
				'0778',
				'0779',
				'0780',
				'0781',
				'0782',
				'0783',
				'0784',
				'0785',
				'0786',
				'0787',
				'0788',
				'0789',
				'0790',
				'0791',
				'0792',
				'0793',
				'0794',
				'0795',
				'0796',
				'0797',
				'0798',
				'0799',
				'0800',
				'0801',
				'0802',
				'0803',
				'0804',
				'0805',
				'0806',
				'0807',
				'0808',
				'0809',
				'0810',
				'0811',
				'0812',
				'0813',
				'0814',
				'0815',
				'0816',
				'0817',
				'0818',
				'0819',
				'0820',
				'0821',
				'0822',
				'0823',
				'0824',
				'0825',
				'0826',
				'0827',
				'0828',
				'0829',
				'0830',
				'0831',
				'0832',
				'0833',
				'0834',
				'0835',
				'0836',
				'0837',
				'0838',
				'0839',
				'0840',
				'0841',
				'0842',
				'0843',
				'0844',
				'0845',
				'0846',
				'0847',
				'0848',
				'0849',
				'0850',
				'0851',
				'0852',
				'0853',
				'0854',
				'0855',
				'0856',
				'0857',
				'0858',
				'0859',
				'0860',
				'0861',
				'0862',
				'0863',
				'0864',
				'0865',
				'0866',
				'0867',
				'0868',
				'0869',
				'0870',
				'0871',
				'0872',
				'0873',
				'0874',
				'0875',
				'0876',
				'0877',
				'rules.0',
				'rules.1',
				'rules.2',
				'rules.3',
			],
		],
		'PHPStan\Rules\Debug\FileAssertRule' => [['0135']],
		'PHPStan\Rules\Debug\DumpPhpDocTypeRule' => [['0136']],
		'PHPStan\Rules\Debug\DumpTypeRule' => [['0137']],
		'PHPStan\Rules\Debug\DumpNativeTypeRule' => [['0138']],
		'PHPStan\Rules\Debug\DebugScopeRule' => [['0139']],
		'PHPStan\Rules\Classes\LocalTypeAliasesCheck' => [['0140']],
		'PHPStan\Rules\Classes\PropertyTagCheck' => [['0141']],
		'PHPStan\Rules\Classes\MixinCheck' => [['0142']],
		'PHPStan\Rules\Classes\DuplicateDeclarationHelper' => [['0143']],
		'PHPStan\Rules\Classes\MethodTagCheck' => [['0144']],
		'PHPStan\Rules\Classes\ConsistentConstructorHelper' => [['0145']],
		'PHPStan\Rules\FunctionCallParametersCheck' => [['0146']],
		'PHPStan\Rules\IssetCheck' => [['0147']],
		'PHPStan\Rules\ParameterCastableToStringCheck' => [['0148']],
		'PHPStan\Rules\Api\ApiRuleHelper' => [['0149']],
		'PHPStan\Rules\ClassCaseSensitivityCheck' => [['0150']],
		'PHPStan\Rules\DeadCode\PossiblyPureCallTransitivePurityResolver' => [['0151']],
		'PHPStan\Rules\UnusedParametersCheck' => [['0152']],
		'PHPStan\Rules\MissingTypehintCheck' => [['0153']],
		'PHPStan\Rules\TooWideTypehints\TooWideTypeCheck' => [['0154']],
		'PHPStan\Rules\TooWideTypehints\TooWideParameterOutTypeCheck' => [['0155']],
		'PHPStan\Rules\Pure\FunctionPurityCheck' => [['0156']],
		'PHPStan\Rules\RestrictedUsage\RestrictedMethodUsageRule' => [['0157']],
		'PHPStan\Rules\RestrictedUsage\RestrictedFunctionCallableUsageRule' => [['0158']],
		'PHPStan\Rules\RestrictedUsage\RestrictedClassConstantUsageRule' => [['0159']],
		'PHPStan\Rules\RestrictedUsage\RestrictedStaticMethodUsageRule' => [['0160']],
		'PHPStan\Rules\RestrictedUsage\RestrictedFunctionUsageRule' => [['0161']],
		'PHPStan\Rules\RestrictedUsage\RestrictedStaticMethodCallableUsageRule' => [['0162']],
		'PHPStan\Rules\RestrictedUsage\RestrictedPropertyUsageRule' => [['0163']],
		'PHPStan\Rules\RestrictedUsage\RestrictedUsageOfDeprecatedStringCastRule' => [['0164']],
		'PHPStan\Rules\RestrictedUsage\RestrictedMethodCallableUsageRule' => [['0165']],
		'PHPStan\Rules\RestrictedUsage\RestrictedStaticPropertyUsageRule' => [['0166']],
		'PHPStan\Rules\AttributesCheck' => [['0167']],
		'PHPStan\Rules\Methods\ParentMethodHelper' => [['0168']],
		'PHPStan\Rules\Methods\MethodCallCheck' => [['0169']],
		'PHPStan\Rules\Methods\MethodVisibilityComparisonHelper' => [['0170']],
		'PHPStan\Rules\Methods\StaticMethodCallCheck' => [['0171']],
		'PHPStan\Rules\Methods\MethodPrototypeFinder' => [['0172']],
		'PHPStan\Rules\Methods\MethodSignatureRule' => [['0173']],
		'PHPStan\Rules\Methods\MethodParameterComparisonHelper' => [['0174']],
		'PHPStan\Rules\FunctionReturnTypeCheck' => [['0175']],
		'PHPStan\Rules\Generics\GenericAncestorsCheck' => [['0176']],
		'PHPStan\Rules\Generics\GenericObjectTypeCheck' => [['0177']],
		'PHPStan\Rules\Generics\TemplateTypeCheck' => [['0178']],
		'PHPStan\Rules\Generics\MethodTagTemplateTypeCheck' => [['0179']],
		'PHPStan\Rules\Generics\CrossCheckInterfacesHelper' => [['0180']],
		'PHPStan\Rules\Generics\VarianceCheck' => [['0181']],
		'PHPStan\Rules\Playground\NeverRuleHelper' => [['0182']],
		'PHPStan\Rules\FunctionDefinitionCheck' => [['0183']],
		'PHPStan\Internal\HttpClientFactory' => [['0184']],
		'PHPStan\Analyser\PropertyHookThrowPointsResolver' => [['0185']],
		'PHPStan\Analyser\PhpDocsResolver' => [['0186']],
		'PHPStan\Analyser\ConstantResolver' => [['0187']],
		'PHPStan\Analyser\Analyser' => [['0188']],
		'PHPStan\Analyser\RuleErrorTransformer' => [['0189']],
		'PHPStan\Analyser\ClosureProcessor' => [['0190']],
		'PHPStan\Analyser\ScopeFactory' => [['0191']],
		'PHPStan\Analyser\PropertyHooksProcessor' => [['0192']],
		'PHPStan\Analyser\NodeScopeResolver' => [['0193']],
		'PHPStan\Analyser\AttributesHandler' => [['0194']],
		'PHPStan\Analyser\ConstantResolverFactory' => [['0195']],
		'PHPStan\Analyser\ExprHandler' => [
			[
				'0196',
				'0197',
				'0198',
				'0199',
				'0200',
				'0201',
				'0202',
				'0203',
				'0204',
				'0205',
				'0206',
				'0207',
				'0208',
				'0209',
				'0210',
				'0211',
				'0212',
				'0213',
				'0214',
				'0215',
				'0216',
				'0217',
				'0218',
				'0219',
				'0220',
				'0221',
				'0222',
				'0223',
				'0224',
				'0225',
				'0226',
				'0227',
				'0228',
				'0229',
				'0230',
				'0231',
				'0232',
				'0233',
				'0234',
				'0235',
				'0236',
				'0237',
				'0238',
				'0239',
				'0240',
				'0241',
				'0242',
				'0243',
				'0244',
				'0245',
				'0246',
				'0247',
				'0248',
				'0249',
				'0250',
				'0251',
				'0252',
				'0253',
				'0254',
				'0255',
				'0256',
			],
		],
		'PHPStan\Analyser\ExprHandler\StaticPropertyFetchHandler' => [['0196']],
		'PHPStan\Analyser\ExprHandler\CoalesceHandler' => [['0197']],
		'PHPStan\Analyser\ExprHandler\EmptyHandler' => [['0198']],
		'PHPStan\Analyser\ExprHandler\PreDecHandler' => [['0199']],
		'PHPStan\Analyser\ExprHandler\ExitHandler' => [['0200']],
		'PHPStan\Analyser\ExprHandler\UnaryPlusHandler' => [['0201']],
		'PHPStan\Analyser\ExprHandler\ArrayHandler' => [['0202']],
		'PHPStan\Analyser\ExprHandler\ThrowHandler' => [['0203']],
		'PHPStan\Analyser\ExprHandler\IssetHandler' => [['0204']],
		'PHPStan\Analyser\ExprHandler\ShellExecHandler' => [['0205']],
		'PHPStan\Analyser\ExprHandler\ClosureHandler' => [['0206']],
		'PHPStan\Analyser\ExprHandler\PostDecHandler' => [['0207']],
		'PHPStan\Analyser\ExprHandler\AssignHandler' => [['0208']],
		'PHPStan\Analyser\ExprHandler\YieldFromHandler' => [['0209']],
		'PHPStan\Analyser\ExprHandler\CastHandler' => [['0210']],
		'PHPStan\Analyser\ExprHandler\BinaryOpHandler' => [['0211']],
		'PHPStan\Analyser\ExprHandler\ArrowFunctionHandler' => [['0212']],
		'PHPStan\Analyser\PerFileAnalysisResettable' => [['0213', '0245', '0264', '0265']],
		'PHPStan\Analyser\ExprHandler\MatchHandler' => [['0213']],
		'PHPStan\Analyser\ExprHandler\Virtual\MethodCallableNodeHandler' => [['0214']],
		'PHPStan\Analyser\ExprHandler\Virtual\InstantiationCallableNodeHandler' => [['0215']],
		'PHPStan\Analyser\ExprHandler\Virtual\TypeExprHandler' => [['0216']],
		'PHPStan\Analyser\ExprHandler\Virtual\FunctionCallableNodeHandler' => [['0217']],
		'PHPStan\Analyser\ExprHandler\Virtual\NativeTypeExprHandler' => [['0218']],
		'PHPStan\Analyser\ExprHandler\Virtual\AlwaysRememberedExprHandler' => [['0219']],
		'PHPStan\Analyser\ExprHandler\Virtual\IssetExprHandler' => [['0220']],
		'PHPStan\Analyser\ExprHandler\Virtual\SetExistingOffsetValueTypeExprHandler' => [['0221']],
		'PHPStan\Analyser\ExprHandler\Virtual\ExistingArrayDimFetchHandler' => [['0222']],
		'PHPStan\Analyser\ExprHandler\Virtual\UnsetOffsetExprHandler' => [['0223']],
		'PHPStan\Analyser\ExprHandler\Virtual\StaticMethodCallableNodeHandler' => [['0224']],
		'PHPStan\Analyser\ExprHandler\Virtual\SetOffsetValueTypeExprHandler' => [['0225']],
		'PHPStan\Analyser\ExprHandler\UnaryMinusHandler' => [['0226']],
		'PHPStan\Analyser\ExprHandler\PipeHandler' => [['0227']],
		'PHPStan\Analyser\ExprHandler\InterpolatedStringHandler' => [['0228']],
		'PHPStan\Analyser\ExprHandler\PreIncHandler' => [['0229']],
		'PHPStan\Analyser\ExprHandler\BooleanAndHandler' => [['0230']],
		'PHPStan\Analyser\ExprHandler\YieldHandler' => [['0231']],
		'PHPStan\Analyser\ExprHandler\PrintHandler' => [['0232']],
		'PHPStan\Analyser\ExprHandler\ScalarHandler' => [['0233']],
		'PHPStan\Analyser\ExprHandler\StaticCallHandler' => [['0234']],
		'PHPStan\Analyser\ExprHandler\NullsafePropertyFetchHandler' => [['0235']],
		'PHPStan\Analyser\ExprHandler\ArrayDimFetchHandler' => [['0236']],
		'PHPStan\Analyser\ExprHandler\ClassConstFetchHandler' => [['0237']],
		'PHPStan\Analyser\ExprHandler\BitwiseNotHandler' => [['0238']],
		'PHPStan\Analyser\ExprHandler\BooleanOrHandler' => [['0239']],
		'PHPStan\Analyser\ExprHandler\AssignOpHandler' => [['0240']],
		'PHPStan\Analyser\ExprHandler\PropertyFetchHandler' => [['0241']],
		'PHPStan\Analyser\ExprHandler\NullsafeMethodCallHandler' => [['0242']],
		'PHPStan\Analyser\ExprHandler\ConstFetchHandler' => [['0243']],
		'PHPStan\Analyser\ExprHandler\IncludeHandler' => [['0244']],
		'PHPStan\Analyser\ExprHandler\TernaryHandler' => [['0245']],
		'PHPStan\Analyser\ExprHandler\NewHandler' => [['0246']],
		'PHPStan\Analyser\ExprHandler\ErrorSuppressHandler' => [['0247']],
		'PHPStan\Analyser\ExprHandler\VariableHandler' => [['0248']],
		'PHPStan\Analyser\ExprHandler\CastStringHandler' => [['0249']],
		'PHPStan\Analyser\ExprHandler\FuncCallHandler' => [['0250']],
		'PHPStan\Analyser\ExprHandler\CloneHandler' => [['0251']],
		'PHPStan\Analyser\ExprHandler\PostIncHandler' => [['0252']],
		'PHPStan\Analyser\ExprHandler\MethodCallHandler' => [['0253']],
		'PHPStan\Analyser\ExprHandler\EvalHandler' => [['0254']],
		'PHPStan\Analyser\ExprHandler\BooleanNotHandler' => [['0255']],
		'PHPStan\Analyser\ExprHandler\InstanceofHandler' => [['0256']],
		'PHPStan\Analyser\ExprHandler\Helper\OutputBufferHelper' => [['0257']],
		'PHPStan\Analyser\ExprHandler\Helper\EarlyTerminatingCallHelper' => [['0258']],
		'PHPStan\Analyser\ExprHandler\Helper\CountNarrowingHelper' => [['0259']],
		'PHPStan\Analyser\ExprHandler\Helper\ForeachEmptyOrObjectNarrowingHelper' => [['0260']],
		'PHPStan\Analyser\ExprHandler\Helper\CoalesceCompositionHelper' => [['0261']],
		'PHPStan\Analyser\ExprHandler\Helper\ConditionalExpressionHolderHelper' => [['0262']],
		'PHPStan\Analyser\ExprHandler\Helper\DynamicReturnTypeStoragePrimer' => [['0263']],
		'PHPStan\Analyser\ExprHandler\Helper\NonNullabilityHelper' => [['0264']],
		'PHPStan\Analyser\ExprHandler\Helper\ClosureTypeResolver' => [['0265']],
		'PHPStan\Analyser\ExprHandler\Helper\ContextualClosureParameterResolver' => [['0266']],
		'PHPStan\Analyser\ExprHandler\Helper\FuncCallScopeEffectsHelper' => [['0267']],
		'PHPStan\Analyser\ExprHandler\Helper\IncDecTypeHelper' => [['0268']],
		'PHPStan\Analyser\ExprHandler\Helper\MethodThrowPointHelper' => [['0269']],
		'PHPStan\Analyser\ExprHandler\Helper\DefaultNarrowingHelper' => [['0270']],
		'PHPStan\Analyser\ExprHandler\Helper\ImplicitToStringCallHelper' => [['0271']],
		'PHPStan\Analyser\ExprHandler\Helper\BooleanNarrowingHelper' => [['0272']],
		'PHPStan\Analyser\ExprHandler\Helper\MethodCallReturnTypeHelper' => [['0273']],
		'PHPStan\Analyser\ExprHandler\Helper\ClosureParameterResolver' => [['0274']],
		'PHPStan\Analyser\ExprHandler\Helper\IdenticalNarrowingHelper' => [['0275']],
		'PHPStan\Analyser\ExprHandler\Helper\VirtualExprResultHelper' => [['0276']],
		'PHPStan\Analyser\CalledMethodProcessor' => [['0277']],
		'PHPStan\Analyser\Ignore\IgnoredErrorHelper' => [['0278']],
		'PHPStan\Analyser\Ignore\IgnoreLexer' => [['0279']],
		'PHPStan\Analyser\StatementsHandler' => [['0280']],
		'PHPStan\Analyser\ArgumentsHandler' => [['0281']],
		'PHPStan\Analyser\DeclarationDependencyTracker' => [['0282']],
		'PHPStan\Analyser\ValueDependencyDeclarationDependencyTracker' => [['0282']],
		'PHPStan\Analyser\StmtHandler' => [
			[
				'0283',
				'0284',
				'0285',
				'0286',
				'0287',
				'0288',
				'0289',
				'0290',
				'0291',
				'0292',
				'0293',
				'0294',
				'0295',
				'0296',
				'0297',
				'0298',
				'0299',
				'0300',
				'0301',
				'0302',
				'0303',
				'0304',
				'0305',
				'0306',
				'0307',
				'0308',
				'0309',
				'0310',
				'0311',
				'0312',
				'0313',
				'0314',
			],
		],
		'PHPStan\Analyser\StmtHandler\NopHandler' => [['0283']],
		'PHPStan\Analyser\StmtHandler\NamespaceHandler' => [['0284']],
		'PHPStan\Analyser\StmtHandler\TraitHandler' => [['0285']],
		'PHPStan\Analyser\StmtHandler\LabelHandler' => [['0286']],
		'PHPStan\Analyser\StmtHandler\ExpressionHandler' => [['0287']],
		'PHPStan\Analyser\StmtHandler\UnsetHandler' => [['0288']],
		'PHPStan\Analyser\StmtHandler\DeclareHandler' => [['0289']],
		'PHPStan\Analyser\StmtHandler\ReturnHandler' => [['0290']],
		'PHPStan\Analyser\StmtHandler\EnumCaseHandler' => [['0291']],
		'PHPStan\Analyser\StmtHandler\FunctionHandler' => [['0292']],
		'PHPStan\Analyser\StmtHandler\PropertyHandler' => [['0293']],
		'PHPStan\Analyser\StmtHandler\ClassConstHandler' => [['0294']],
		'PHPStan\Analyser\StmtHandler\ForHandler' => [['0295']],
		'PHPStan\Analyser\StmtHandler\ForeachHandler' => [['0296']],
		'PHPStan\Analyser\StmtHandler\DoWhileHandler' => [['0297']],
		'PHPStan\Analyser\StmtHandler\GroupUseHandler' => [['0298']],
		'PHPStan\Analyser\StmtHandler\BreakContinueHandler' => [['0299']],
		'PHPStan\Analyser\StmtHandler\BlockHandler' => [['0300']],
		'PHPStan\Analyser\StmtHandler\ConstHandler' => [['0301']],
		'PHPStan\Analyser\StmtHandler\SwitchHandler' => [['0302']],
		'PHPStan\Analyser\StmtHandler\GotoHandler' => [['0303']],
		'PHPStan\Analyser\StmtHandler\UseHandler' => [['0304']],
		'PHPStan\Analyser\StmtHandler\IfHandler' => [['0305']],
		'PHPStan\Analyser\StmtHandler\ClassLikeHandler' => [['0306']],
		'PHPStan\Analyser\StmtHandler\TryCatchHandler' => [['0307']],
		'PHPStan\Analyser\StmtHandler\ClassMethodHandler' => [['0308']],
		'PHPStan\Analyser\StmtHandler\EchoHandler' => [['0309']],
		'PHPStan\Analyser\StmtHandler\WhileHandler' => [['0310']],
		'PHPStan\Analyser\StmtHandler\GlobalHandler' => [['0311']],
		'PHPStan\Analyser\StmtHandler\StaticVariableHandler' => [['0312']],
		'PHPStan\Analyser\StmtHandler\InlineHtmlHandler' => [['0313']],
		'PHPStan\Analyser\StmtHandler\TraitUseHandler' => [['0314']],
		'PHPStan\Analyser\VarAnnotationProcessor' => [['0315']],
		'PHPStan\Analyser\AnalyserResultFinalizer' => [['0316']],
		'PHPStan\Analyser\LocalIgnoresProcessor' => [['0317']],
		'PHPStan\Analyser\DeprecatedAttributeResolver' => [['0318']],
		'PHPStan\Analyser\ParametersProcessor' => [['0319']],
		'PHPStan\Analyser\TypeSpecifier' => [['typeSpecifier']],
		'PHPStan\Analyser\ValueDependencyCollector' => [['0320']],
		'PHPStan\Analyser\FileAnalyser' => [['0321']],
		'PHPStan\Analyser\TypeSpecifierFactory' => [['typeSpecifierFactory']],
		'PHPStan\Analyser\ResultCache\ResultCacheValueExtension' => [['0322', '0324', '0325', '0375']],
		'PHPStan\Analyser\ResultCache\ClassResultCacheValueExtension' => [['0322']],
		'PHPStan\Analyser\ResultCache\ResultCacheClearer' => [['0323']],
		'PHPStan\Analyser\ResultCache\FileResultCacheValueExtension' => [['0324']],
		'PHPStan\Analyser\ResultCache\DirectoryResultCacheValueExtension' => [['0325']],
		'PHPStan\Analyser\RicherScopeGetTypeHelper' => [['0326']],
		'PHPStan\Analyser\Generics\TemplateArgumentResolver' => [['0327']],
		'PHPStan\Analyser\Generics\TemplateArgumentObserver' => [['0328']],
		'PHPStan\Analyser\Generics\StaticVariableInference' => [['0329']],
		'PHPStan\Analyser\Generics\ClosureSignatureInference' => [['0330']],
		'PHPStan\DependencyInjection\Reflection\ClassReflectionExtensionRegistryProvider' => [['0331']],
		'PHPStan\DependencyInjection\Reflection\LazyClassReflectionExtensionRegistryProvider' => [['0331']],
		'PHPStan\DependencyInjection\DerivativeContainerFactory' => [['0332']],
		'PHPStan\DependencyInjection\Container' => [['0333'], ['0334']],
		'PHPStan\DependencyInjection\MemoizingContainer' => [['0333']],
		'PHPStan\DependencyInjection\Nette\NetteContainer' => [['0334']],
		'PHPStan\Collectors\RegistryFactory' => [['0335']],
		'PHPStan\Collectors\Registry' => [['0336']],
		'PHPStan\Fixable\PhpDoc\PhpDocEditor' => [['0337']],
		'PHPStan\Fixable\Patcher' => [['0338']],
		'PHPStan\Command\ErrorFormatter\ErrorFormatter' => [
			[
				'errorFormatter.gitlab',
				'errorFormatter.checkstyle',
				'errorFormatter.teamcity',
				'errorFormatter.github',
				'errorFormatter.table',
				'errorFormatter.raw',
				'errorFormatter.junit',
				'errorFormatter.json',
				'errorFormatter.prettyJson',
			],
			['0339'],
		],
		'PHPStan\Command\ErrorFormatter\GitlabErrorFormatter' => [['errorFormatter.gitlab']],
		'PHPStan\Command\ErrorFormatter\CheckstyleErrorFormatter' => [['errorFormatter.checkstyle']],
		'PHPStan\Command\ErrorFormatter\TeamcityErrorFormatter' => [['errorFormatter.teamcity']],
		'PHPStan\Command\ErrorFormatter\GithubErrorFormatter' => [['errorFormatter.github']],
		'PHPStan\Command\ErrorFormatter\CiDetectedErrorFormatter' => [['0339']],
		'PHPStan\Command\ErrorFormatter\TableErrorFormatter' => [['errorFormatter.table']],
		'PHPStan\Command\ErrorFormatter\RawErrorFormatter' => [['errorFormatter.raw']],
		'PHPStan\Command\ErrorFormatter\JunitErrorFormatter' => [['errorFormatter.junit']],
		'PHPStan\Command\AnalyseApplication' => [['0340']],
		'PHPStan\Command\FixerWorkerRunner' => [['0341']],
		'PHPStan\Command\BootstrapFilesRunner' => [['0342']],
		'PHPStan\Command\AnalyserRunner' => [['0343']],
		'PHPStan\Command\FixerApplication' => [['0344']],
		'PHPStan\Type\Regex\RegexExpressionHelper' => [['0345']],
		'PHPStan\Type\Regex\RegexGroupParser' => [['0346']],
		'PHPStan\Type\DynamicFunctionReturnTypeExtension' => [
			[
				'0347',
				'0351',
				'0355',
				'0356',
				'0357',
				'0359',
				'0360',
				'0365',
				'0367',
				'0369',
				'0372',
				'0377',
				'0378',
				'0380',
				'0383',
				'0384',
				'0385',
				'0388',
				'0389',
				'0391',
				'0392',
				'0393',
				'0395',
				'0396',
				'0397',
				'0399',
				'0401',
				'0402',
				'0410',
				'0411',
				'0413',
				'0415',
				'0419',
				'0423',
				'0425',
				'0427',
				'0428',
				'0429',
				'0430',
				'0432',
				'0433',
				'0436',
				'0437',
				'0438',
				'0440',
				'0442',
				'0444',
				'0445',
				'0446',
				'0447',
				'0448',
				'0449',
				'0457',
				'0458',
				'0459',
				'0460',
				'0461',
				'0462',
				'0463',
				'0464',
				'0466',
				'0468',
				'0471',
				'0474',
				'0477',
				'0478',
				'0479',
				'0480',
				'0482',
				'0483',
				'0485',
				'0488',
				'0489',
				'0490',
				'0491',
				'0493',
				'0494',
				'0495',
				'0498',
				'0499',
				'0502',
				'0503',
				'0504',
				'0505',
				'0508',
				'0509',
				'0510',
				'0511',
				'0512',
				'0513',
				'0521',
				'0522',
				'0524',
				'0526',
				'0529',
				'0530',
				'0531',
				'0533',
				'0535',
				'0536',
				'0537',
				'0539',
				'0542',
				'0543',
				'0544',
				'0546',
				'0547',
				'0548',
				'0549',
				'0550',
				'0551',
				'0975',
				'0976',
				'0977',
				'0978',
				'0979',
				'0980',
				'0988',
				'0989',
				'0990',
				'0991',
				'01050',
				'01051',
				'01056',
				'01061',
			],
		],
		'PHPStan\Type\Php\StrvalFamilyFunctionReturnTypeExtension' => [['0347']],
		'PHPStan\Type\DynamicFunctionThrowTypeExtension' => [
			[
				'0348',
				'0368',
				'0394',
				'0404',
				'0406',
				'0407',
				'0421',
				'0431',
				'0434',
				'0501',
				'0506',
				'0515',
				'0517',
				'0540',
			],
		],
		'PHPStan\Type\Php\IntdivThrowTypeExtension' => [['0348']],
		'PHPStan\Type\DynamicMethodThrowTypeExtension' => [['0349', '0373', '0379', '0454']],
		'PHPStan\Type\Php\DateTimeModifyMethodThrowTypeExtension' => [['0349']],
		'PHPStan\Type\Php\DateFormatMethodReturnTypeExtension' => [['0350']],
		'PHPStan\Type\Php\ArrayNextDynamicReturnTypeExtension' => [['0351']],
		'PHPStan\Type\Php\PrintfFormatParser' => [['0352']],
		'PHPStan\Type\FunctionTypeSpecifyingExtension' => [
			[
				'0353',
				'0358',
				'0363',
				'0364',
				'0387',
				'0400',
				'0417',
				'0422',
				'0424',
				'0426',
				'0435',
				'0453',
				'0467',
				'0469',
				'0486',
				'0514',
				'0518',
				'0525',
				'0527',
				'0528',
				'0538',
				'0984',
				'0985',
				'0986',
				'0987',
			],
		],
		'PHPStan\Analyser\TypeSpecifierAwareExtension' => [
			[
				'0353',
				'0358',
				'0363',
				'0364',
				'0366',
				'0387',
				'0400',
				'0417',
				'0422',
				'0424',
				'0426',
				'0435',
				'0453',
				'0467',
				'0469',
				'0486',
				'0514',
				'0518',
				'0525',
				'0527',
				'0528',
				'0538',
				'0984',
				'0985',
				'0986',
				'0987',
			],
		],
		'PHPStan\Type\Php\IsIterableFunctionTypeSpecifyingExtension' => [['0353']],
		'PHPStan\Type\DynamicStaticMethodReturnTypeExtension' => [
			[
				'0354',
				'0408',
				'0409',
				'0416',
				'0441',
				'0472',
				'0473',
				'0496',
				'0507',
				'0960',
				'0961',
				'0962',
				'0963',
				'0964',
				'0966',
				'0992',
				'01006',
				'01049',
				'01058',
			],
		],
		'PHPStan\Type\Php\ClosureGetCurrentDynamicReturnTypeExtension' => [['0354']],
		'PHPStan\Type\Php\ArrayIntersectKeyFunctionReturnTypeExtension' => [['0355']],
		'PHPStan\Type\Php\HashFunctionsReturnTypeExtension' => [['0356']],
		'PHPStan\Type\Php\Base64DecodeDynamicFunctionReturnTypeExtension' => [['0357']],
		'PHPStan\Type\Php\InArrayFunctionTypeSpecifyingExtension' => [['0358']],
		'PHPStan\Type\Php\CompactFunctionReturnTypeExtension' => [['0359']],
		'PHPStan\Type\Php\AbsFunctionDynamicReturnTypeExtension' => [['0360']],
		'PHPStan\Type\Php\DsMapDynamicReturnTypeExtension' => [['0361']],
		'PHPStan\Type\DynamicStaticMethodThrowTypeExtension' => [
			['0362', '0381', '0420', '0451', '0465', '0497', '0500', '0516', '0523', '0532'],
		],
		'PHPStan\Type\Php\DateTimeConstructorThrowTypeExtension' => [['0362']],
		'PHPStan\Type\Php\ClassExistsFunctionTypeSpecifyingExtension' => [['0363']],
		'PHPStan\Type\Php\IsArrayFunctionTypeSpecifyingExtension' => [['0364']],
		'PHPStan\Type\Php\StrSplitFunctionReturnTypeExtension' => [['0365']],
		'PHPStan\Type\MethodTypeSpecifyingExtension' => [['0366']],
		'PHPStan\Type\Php\ReflectionClassIsSubclassOfTypeSpecifyingExtension' => [['0366']],
		'PHPStan\Type\Php\ArrayReverseFunctionReturnTypeExtension' => [['0367']],
		'PHPStan\Type\Php\ArrayCombineFunctionThrowTypeExtension' => [['0368']],
		'PHPStan\Type\Php\ArrayFillKeysFunctionReturnTypeExtension' => [['0369']],
		'PHPStan\Type\Php\ConstantHelper' => [['0370']],
		'PHPStan\Type\Php\DateIntervalFormatReturnTypeHelper' => [['0371']],
		'PHPStan\Type\Php\StrlenFunctionReturnTypeExtension' => [['0372']],
		'PHPStan\Type\Php\DomDocumentCreateElementDynamicThrowTypeExtension' => [['0373']],
		'PHPStan\Type\Php\SimpleXMLElementAsXMLMethodReturnTypeExtension' => [['0374']],
		'PHPStan\Type\Php\OpenSslCipherMethodsProvider' => [['0375']],
		'PHPStan\Type\Php\ArrayColumnHelper' => [['0376']],
		'PHPStan\Type\Php\LtrimFunctionReturnTypeExtension' => [['0377']],
		'PHPStan\Type\Php\GettypeFunctionReturnTypeExtension' => [['0378']],
		'PHPStan\Type\Php\DateTimeSubMethodThrowTypeExtension' => [['0379']],
		'PHPStan\Type\Php\JsonThrowOnErrorDynamicReturnTypeExtension' => [['0380']],
		'PHPStan\Type\Php\ReflectionMethodConstructorThrowTypeExtension' => [['0381']],
		'PHPStan\Type\Php\ClosureBindToDynamicReturnTypeExtension' => [['0382']],
		'PHPStan\Type\Php\DateTimeDynamicReturnTypeExtension' => [['0383']],
		'PHPStan\Type\Php\IdateFunctionReturnTypeExtension' => [['0384']],
		'PHPStan\Type\Php\ExplodeFunctionDynamicReturnTypeExtension' => [['0385']],
		'PHPStan\Type\Php\RegexArrayShapeMatcher' => [['0386']],
		'PHPStan\Type\Php\IsAFunctionTypeSpecifyingExtension' => [['0387']],
		'PHPStan\Type\Php\ParseUrlFunctionDynamicReturnTypeExtension' => [['0388']],
		'PHPStan\Type\Php\CountFunctionReturnTypeExtension' => [['0389']],
		'PHPStan\Type\Php\DomDocumentCreateElementDynamicReturnTypeExtension' => [['0390']],
		'PHPStan\Type\Php\ArrayPointerFunctionsDynamicReturnTypeExtension' => [['0391']],
		'PHPStan\Type\Php\IniGetReturnTypeExtension' => [['0392']],
		'PHPStan\Type\Php\PregFilterFunctionReturnTypeExtension' => [['0393']],
		'PHPStan\Type\Php\GetClassFunctionThrowTypeExtension' => [['0394']],
		'PHPStan\Type\Php\ArrayCountValuesDynamicReturnTypeExtension' => [['0395']],
		'PHPStan\Type\Php\FilterInputDynamicReturnTypeExtension' => [['0396']],
		'PHPStan\Type\Php\StrWordCountFunctionDynamicReturnTypeExtension' => [['0397']],
		'PHPStan\Type\Php\SimpleXMLElementClassPropertyReflectionExtension' => [['0398']],
		'PHPStan\Type\Php\LocaltimeFunctionDynamicReturnTypeExtension' => [['0399']],
		'PHPStan\Type\Php\PregMatchTypeSpecifyingExtension' => [['0400']],
		'PHPStan\Type\Php\DateIntervalFormatFunctionReturnTypeExtension' => [['0401']],
		'PHPStan\Type\Php\ArrayReplaceFunctionReturnTypeExtension' => [['0402']],
		'PHPStan\Type\Php\ThrowableReturnTypeExtension' => [['0403']],
		'PHPStan\Type\Php\JsonThrowTypeExtension' => [['0404']],
		'PHPStan\Type\Php\FilterFunctionFlagsHelper' => [['0405']],
		'PHPStan\Type\Php\MinMaxFunctionThrowTypeExtension' => [['0406']],
		'PHPStan\Type\Php\StrSplitFunctionThrowTypeExtension' => [['0407']],
		'PHPStan\Type\Php\ClosureBindDynamicReturnTypeExtension' => [['0408']],
		'PHPStan\Type\Php\XMLReaderOpenReturnTypeExtension' => [['0409']],
		'PHPStan\Type\Php\ArraySearchFunctionDynamicReturnTypeExtension' => [['0410']],
		'PHPStan\Type\Php\ArrayFirstLastDynamicReturnTypeExtension' => [['0411']],
		'PHPStan\Type\OperatorTypeSpecifyingExtension' => [['0412', '0520']],
		'PHPStan\Type\Php\BcMathNumberOperatorTypeSpecifyingExtension' => [['0412']],
		'PHPStan\Type\Php\BcMathStringOrNullReturnTypeExtension' => [['0413']],
		'PHPStan\Type\FunctionParameterClosureTypeExtension' => [['0414', '0443', '0470', '0487', '0492']],
		'PHPStan\Type\Php\PregReplaceCallbackClosureTypeExtension' => [['0414']],
		'PHPStan\Type\Php\SprintfFunctionDynamicReturnTypeExtension' => [['0415']],
		'PHPStan\Type\Php\ClosureFromCallableDynamicReturnTypeExtension' => [['0416']],
		'PHPStan\Type\Php\MethodExistsTypeSpecifyingExtension' => [['0417']],
		'PHPStan\Type\Php\ArrayFilterFunctionReturnTypeHelper' => [['0418']],
		'PHPStan\Type\Php\ArrayKeyDynamicReturnTypeExtension' => [['0419']],
		'PHPStan\Type\Php\ReflectionClassConstantConstructorThrowTypeExtension' => [['0420']],
		'PHPStan\Type\Php\VersionCompareFunctionDynamicThrowTypeExtension' => [['0421']],
		'PHPStan\Type\Php\AssertFunctionTypeSpecifyingExtension' => [['0422']],
		'PHPStan\Type\Php\MbStrlenFunctionReturnTypeExtension' => [['0423']],
		'PHPStan\Type\Php\ArraySearchFunctionTypeSpecifyingExtension' => [['0424']],
		'PHPStan\Type\Php\ArrayMergeFunctionDynamicReturnTypeExtension' => [['0425']],
		'PHPStan\Type\Php\DefinedConstantTypeSpecifyingExtension' => [['0426']],
		'PHPStan\Type\Php\VersionCompareFunctionDynamicReturnTypeExtension' => [['0427']],
		'PHPStan\Type\Php\ClassImplementsFunctionReturnTypeExtension' => [['0428']],
		'PHPStan\Type\Php\ArraySpliceFunctionReturnTypeExtension' => [['0429']],
		'PHPStan\Type\Php\StrtotimeFunctionReturnTypeExtension' => [['0430']],
		'PHPStan\Type\Php\FilterFunctionsThrowTypeExtension' => [['0431']],
		'PHPStan\Type\Php\MinMaxFunctionReturnTypeExtension' => [['0432']],
		'PHPStan\Type\Php\PowFunctionReturnTypeExtension' => [['0433']],
		'PHPStan\Type\Php\AssertThrowTypeExtension' => [['0434']],
		'PHPStan\Type\Php\DefineConstantTypeSpecifyingExtension' => [['0435']],
		'PHPStan\Type\Php\NonEmptyStringFunctionsReturnTypeExtension' => [['0436']],
		'PHPStan\Type\Php\IteratorToArrayFunctionReturnTypeExtension' => [['0437']],
		'PHPStan\Type\Php\PregSplitDynamicReturnTypeExtension' => [['0438']],
		'PHPStan\Type\Php\IsAFunctionTypeSpecifyingHelper' => [['0439']],
		'PHPStan\Type\Php\ArgumentBasedFunctionReturnTypeExtension' => [['0440']],
		'PHPStan\Type\Php\DateIntervalDynamicReturnTypeExtension' => [['0441']],
		'PHPStan\Type\Php\ArrayFilterFunctionReturnTypeExtension' => [['0442']],
		'PHPStan\Type\Php\ArrayFindParameterClosureTypeExtension' => [['0443']],
		'PHPStan\Type\Php\FilterVarDynamicReturnTypeExtension' => [['0444']],
		'PHPStan\Type\Php\SscanfFunctionDynamicReturnTypeExtension' => [['0445']],
		'PHPStan\Type\Php\StrPadFunctionReturnTypeExtension' => [['0446']],
		'PHPStan\Type\Php\GetCalledClassDynamicReturnTypeExtension' => [['0447']],
		'PHPStan\Type\Php\StrShuffleFunctionReturnTypeExtension' => [['0448']],
		'PHPStan\Type\Php\SubstrDynamicReturnTypeExtension' => [['0449']],
		'PHPStan\Type\FunctionParameterOutTypeExtension' => [['0450', '0452', '0476']],
		'PHPStan\Type\Php\ParseStrParameterOutTypeExtension' => [['0450']],
		'PHPStan\Type\Php\ReflectionClassConstructorThrowTypeExtension' => [['0451']],
		'PHPStan\Type\Php\OpenSslEncryptParameterOutTypeExtension' => [['0452']],
		'PHPStan\Type\Php\IsCallableFunctionTypeSpecifyingExtension' => [['0453']],
		'PHPStan\Type\Php\DsMapDynamicMethodThrowTypeExtension' => [['0454']],
		'PHPStan\Type\Php\RandomizerMethodReturnTypeExtension' => [['0455']],
		'PHPStan\Type\Php\DateFunctionReturnTypeHelper' => [['0456']],
		'PHPStan\Type\Php\RandomIntFunctionReturnTypeExtension' => [['0457']],
		'PHPStan\Type\Php\ArrayPopFunctionReturnTypeExtension' => [['0458']],
		'PHPStan\Type\Php\TriggerErrorDynamicReturnTypeExtension' => [['0459']],
		'PHPStan\Type\Php\NumberFormatFunctionDynamicReturnTypeExtension' => [['0460']],
		'PHPStan\Type\Php\ArrayChangeKeyCaseFunctionReturnTypeExtension' => [['0461']],
		'PHPStan\Type\Php\StrrevFunctionReturnTypeExtension' => [['0462']],
		'PHPStan\Type\Php\ArrayCurrentDynamicReturnTypeExtension' => [['0463']],
		'PHPStan\Type\Php\ArraySliceFunctionReturnTypeExtension' => [['0464']],
		'PHPStan\Type\Php\DateIntervalConstructorThrowTypeExtension' => [['0465']],
		'PHPStan\Type\Php\PgDmlDynamicReturnTypeExtension' => [['0466']],
		'PHPStan\Type\Php\IsSubclassOfFunctionTypeSpecifyingExtension' => [['0467']],
		'PHPStan\Type\Php\CurlGetinfoFunctionDynamicReturnTypeExtension' => [['0468']],
		'PHPStan\Type\Php\PropertyExistsTypeSpecifyingExtension' => [['0469']],
		'PHPStan\Type\Php\ArrayMapParameterClosureTypeExtension' => [['0470']],
		'PHPStan\Type\Php\MbConvertEncodingFunctionReturnTypeExtension' => [['0471']],
		'PHPStan\Type\Php\BackedEnumFromMethodDynamicReturnTypeExtension' => [['0472']],
		'PHPStan\Type\Php\PDOConnectReturnTypeExtension' => [['0473']],
		'PHPStan\Type\Php\GetDefinedVarsFunctionReturnTypeExtension' => [['0474']],
		'PHPStan\Type\Php\SimpleXMLElementXpathMethodReturnTypeExtension' => [['0475']],
		'PHPStan\Type\Php\PregMatchParameterOutTypeExtension' => [['0476']],
		'PHPStan\Type\Php\DioStatDynamicFunctionReturnTypeExtension' => [['0477']],
		'PHPStan\Type\Php\ArrayShiftFunctionReturnTypeExtension' => [['0478']],
		'PHPStan\Type\Php\ReplaceFunctionsDynamicReturnTypeExtension' => [['0479']],
		'PHPStan\Type\Php\GetDebugTypeFunctionReturnTypeExtension' => [['0480']],
		'PHPStan\Type\Php\IdateFunctionReturnTypeHelper' => [['0481']],
		'PHPStan\Type\Php\MicrotimeFunctionReturnTypeExtension' => [['0482']],
		'PHPStan\Type\Php\DateFormatFunctionReturnTypeExtension' => [['0483']],
		'PHPStan\Type\UnaryOperatorTypeSpecifyingExtension' => [['0484', '0541']],
		'PHPStan\Type\Php\GmpUnaryOperatorTypeSpecifyingExtension' => [['0484']],
		'PHPStan\Type\Php\ArrayFindKeyFunctionReturnTypeExtension' => [['0485']],
		'PHPStan\Type\Php\CountFunctionTypeSpecifyingExtension' => [['0486']],
		'PHPStan\Type\Php\ArrayFilterParameterClosureTypeExtension' => [['0487']],
		'PHPStan\Type\Php\FilterVarArrayDynamicReturnTypeExtension' => [['0488']],
		'PHPStan\Type\Php\ArrayKeysFunctionDynamicReturnTypeExtension' => [['0489']],
		'PHPStan\Type\Php\StatDynamicReturnTypeExtension' => [['0490']],
		'PHPStan\Type\Php\StrRepeatFunctionReturnTypeExtension' => [['0491']],
		'PHPStan\Type\Php\ArrayWalkParameterClosureTypeExtension' => [['0492']],
		'PHPStan\Type\Php\ArrayFillFunctionReturnTypeExtension' => [['0493']],
		'PHPStan\Type\Php\PgResultStatusDynamicReturnTypeExtension' => [['0494']],
		'PHPStan\Type\Php\ArrayFindFunctionReturnTypeExtension' => [['0495']],
		'PHPStan\Type\Php\ArrayObjectConstructorReturnTypeExtension' => [['0496']],
		'PHPStan\Type\Php\SimpleXMLElementConstructorThrowTypeExtension' => [['0497']],
		'PHPStan\Type\Php\GettimeofdayDynamicFunctionReturnTypeExtension' => [['0498']],
		'PHPStan\Type\Php\DateFunctionReturnTypeExtension' => [['0499']],
		'PHPStan\Type\Php\DateIntervalCreateFromDateStringThrowTypeExtension' => [['0500']],
		'PHPStan\Type\Php\UnserializeFunctionThrowTypeExtension' => [['0501']],
		'PHPStan\Type\Php\OutputBufferingDynamicReturnTypeExtension' => [['0502']],
		'PHPStan\Type\Php\ClampFunctionReturnTypeExtension' => [['0503']],
		'PHPStan\Type\Php\PgLastNoticeDynamicReturnTypeExtension' => [['0504']],
		'PHPStan\Type\Php\GetParentClassDynamicFunctionReturnTypeExtension' => [['0505']],
		'PHPStan\Type\Php\PrintfFunctionThrowTypeExtension' => [['0506']],
		'PHPStan\Type\Php\DatePeriodConstructorReturnTypeExtension' => [['0507']],
		'PHPStan\Type\Php\DateTimeCreateDynamicReturnTypeExtension' => [['0508']],
		'PHPStan\Type\Php\ArrayCombineFunctionReturnTypeExtension' => [['0509']],
		'PHPStan\Type\Php\GetClassDynamicReturnTypeExtension' => [['0510']],
		'PHPStan\Type\Php\ArrayReduceFunctionReturnTypeExtension' => [['0511']],
		'PHPStan\Type\Php\MbSubstituteCharacterDynamicReturnTypeExtension' => [['0512']],
		'PHPStan\Type\Php\ArrayFlipFunctionReturnTypeExtension' => [['0513']],
		'PHPStan\Type\Php\ArrayKeyExistsFunctionTypeSpecifyingExtension' => [['0514']],
		'PHPStan\Type\Php\RoundFunctionThrowTypeExtension' => [['0515']],
		'PHPStan\Type\Php\ReflectionFunctionConstructorThrowTypeExtension' => [['0516']],
		'PHPStan\Type\Php\ArrayChunkFunctionThrowTypeExtension' => [['0517']],
		'PHPStan\Type\Php\SetTypeFunctionTypeSpecifyingExtension' => [['0518']],
		'PHPStan\Type\Php\DateIntervalFormatDynamicReturnTypeExtension' => [['0519']],
		'PHPStan\Type\Php\GmpOperatorTypeSpecifyingExtension' => [['0520']],
		'PHPStan\Type\Php\ArrayChunkFunctionReturnTypeExtension' => [['0521']],
		'PHPStan\Type\Php\HrtimeFunctionReturnTypeExtension' => [['0522']],
		'PHPStan\Type\Php\ReflectionPropertyConstructorThrowTypeExtension' => [['0523']],
		'PHPStan\Type\Php\MbFunctionsReturnTypeExtension' => [['0524']],
		'PHPStan\Type\Php\StrlenFunctionTypeSpecifyingExtension' => [['0525']],
		'PHPStan\Type\Php\StrIncrementDecrementFunctionReturnTypeExtension' => [['0526']],
		'PHPStan\Type\Php\StrContainingTypeSpecifyingExtension' => [['0527']],
		'PHPStan\Type\Php\FunctionExistsFunctionTypeSpecifyingExtension' => [['0528']],
		'PHPStan\Type\Php\ImplodeFunctionReturnTypeExtension' => [['0529']],
		'PHPStan\Type\Php\ArrayRandFunctionReturnTypeExtension' => [['0530']],
		'PHPStan\Type\Php\HighlightStringDynamicReturnTypeExtension' => [['0531']],
		'PHPStan\Type\Php\DateTimeZoneConstructorThrowTypeExtension' => [['0532']],
		'PHPStan\Type\Php\PathinfoFunctionDynamicReturnTypeExtension' => [['0533']],
		'PHPStan\Type\Php\ArrayCombineHelper' => [['0534']],
		'PHPStan\Type\Php\CountCharsFunctionDynamicReturnTypeExtension' => [['0535']],
		'PHPStan\Type\Php\ArrayMapFunctionReturnTypeExtension' => [['0536']],
		'PHPStan\Type\Php\ArrayPadDynamicReturnTypeExtension' => [['0537']],
		'PHPStan\Type\Php\CtypeDigitFunctionTypeSpecifyingExtension' => [['0538']],
		'PHPStan\Type\Php\RoundFunctionReturnTypeExtension' => [['0539']],
		'PHPStan\Type\Php\TriggerErrorFunctionThrowTypeExtension' => [['0540']],
		'PHPStan\Type\Php\BcMathNumberUnaryOperatorTypeSpecifyingExtension' => [['0541']],
		'PHPStan\Type\Php\RangeFunctionReturnTypeExtension' => [['0542']],
		'PHPStan\Type\Php\ArrayValuesFunctionDynamicReturnTypeExtension' => [['0543']],
		'PHPStan\Type\Php\OpensslCipherFunctionsReturnTypeExtension' => [['0544']],
		'PHPStan\Type\Php\FilterFunctionReturnTypeHelper' => [['0545']],
		'PHPStan\Type\Php\TrimFunctionDynamicReturnTypeExtension' => [['0546']],
		'PHPStan\Type\Php\StrTokFunctionReturnTypeExtension' => [['0547']],
		'PHPStan\Type\Php\ConstantFunctionReturnTypeExtension' => [['0548']],
		'PHPStan\Type\Php\ArraySumFunctionDynamicReturnTypeExtension' => [['0549']],
		'PHPStan\Type\Php\StrCaseFunctionsReturnTypeExtension' => [['0550']],
		'PHPStan\Type\Php\ArrayColumnFunctionReturnTypeExtension' => [['0551']],
		'PHPStan\Type\UnaryOperatorTypeSpecifyingExtensionRegistry' => [['0552']],
		'PHPStan\Type\DynamicReturnTypeExtensionRegistry' => [['0553']],
		'PHPStan\Type\OperatorTypeSpecifyingExtensionRegistry' => [['0554']],
		'PHPStan\Type\Constant\OversizedArrayBuilder' => [['0555']],
		'PHPStan\Type\FileTypeMapper' => [0 => ['0556'], 2 => [1 => 'stubFileTypeMapper']],
		'PHPStan\Type\BitwiseFlagHelper' => [['0557']],
		'PHPStan\Type\ArrayUnpackingHelper' => [['0558']],
		'PHPStan\Type\TypeAliasResolverProvider' => [['0559']],
		'PHPStan\Type\LazyTypeAliasResolverProvider' => [['0559']],
		'PHPStan\Type\PHPStan\ClassNameUsageLocationCreateIdentifierDynamicReturnTypeExtension' => [['0560']],
		'PHPStan\Type\ClosureTypeFactory' => [['0561']],
		'PHPStan\Type\TypeAliasResolver' => [['0562']],
		'PHPStan\Type\UsefulTypeAliasResolver' => [['0562']],
		'PHPStan\File\SimpleRelativePathHelper' => [2 => ['simpleRelativePathHelper']],
		'PHPStan\File\ParentDirectoryRelativePathHelper' => [2 => ['parentDirectoryRelativePathHelper']],
		'PHPStan\Reflection\ReflectionProvider' => [0 => ['reflectionProvider'], 2 => ['betterReflectionProvider']],
		'PHPStan\Reflection\BetterReflection\BetterReflectionProvider' => [2 => ['betterReflectionProvider']],
		'PHPStan\Diagnose\PHPStanDiagnoseExtension' => [2 => ['phpstanDiagnoseExtension']],
		'PHPStan\File\FileExcluderRawFactory' => [['0563']],
		'PHPStan\Reflection\Php\PhpMethodReflectionFactory' => [['0564']],
		'PHPStan\Reflection\FunctionReflectionFactory' => [['0565']],
		'PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedSingleFileSourceLocatorFactory' => [['0566']],
		'PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedPsrAutoloaderLocatorFactory' => [['0567']],
		'PHPStan\Reflection\ClassReflectionFactory' => [['0568']],
		'PHPStan\Analyser\ExpressionResultFactory' => [['0569']],
		'PHPStan\Analyser\InternalScopeFactoryFactory' => [['0570']],
		'PHPStan\Analyser\ResultCache\ResultCacheManagerFactory' => [['0571']],
		'PHPStan\Rules\Namespaces\ExistingNamesInUseRule' => [['0572']],
		'PHPStan\Rules\Namespaces\ExistingNamesInGroupUseRule' => [['0573']],
		'PHPStan\Rules\Generators\YieldTypeRule' => [['0574']],
		'PHPStan\Rules\Generators\YieldFromTypeRule' => [['0575']],
		'PHPStan\Rules\MultipleNodeTypesRule' => [
			['0917'],
			['0576', '0582', '0589', '0597', '0620', '0698', '0708', '0830', '0831', '0834', '0836'],
		],
		'PHPStan\Rules\Generators\YieldInGeneratorRule' => [['0576']],
		'PHPStan\Rules\PhpDoc\IncompatiblePropertyHookPhpDocTypeRule' => [['0577']],
		'PHPStan\Rules\PhpDoc\IncompatibleSelfOutTypeRule' => [['0578']],
		'PHPStan\Rules\PhpDoc\SealedDefinitionTraitRule' => [['0579']],
		'PHPStan\Rules\PhpDoc\IncompatibleClassConstantPhpDocTypeRule' => [['0580']],
		'PHPStan\Rules\PhpDoc\FunctionConditionalReturnTypeRule' => [['0581']],
		'PHPStan\Rules\PhpDoc\InvalidPHPStanDocTagRule' => [['0582']],
		'PHPStan\Rules\PhpDoc\InvalidPhpDocVarTagTypeRule' => [['0583']],
		'PHPStan\Rules\PhpDoc\VarTagChangedExpressionTypeRule' => [['0584']],
		'PHPStan\Rules\PhpDoc\SealedDefinitionClassRule' => [['0585']],
		'PHPStan\Rules\PhpDoc\IncompatiblePropertyPhpDocTypeRule' => [['0586']],
		'PHPStan\Rules\PhpDoc\IncompatiblePhpDocTypeRule' => [['0587']],
		'PHPStan\Rules\PhpDoc\FunctionAssertRule' => [['0588']],
		'PHPStan\Rules\PhpDoc\InvalidThrowsPhpDocValueRule' => [['0589']],
		'PHPStan\Rules\PhpDoc\RequireImplementsDefinitionClassRule' => [['0590']],
		'PHPStan\Rules\PhpDoc\RequireExtendsDefinitionClassRule' => [['0591']],
		'PHPStan\Rules\PhpDoc\MethodConditionalReturnTypeRule' => [['0592']],
		'PHPStan\Rules\PhpDoc\RequireImplementsDefinitionTraitRule' => [['0593']],
		'PHPStan\Rules\PhpDoc\IncompatibleParamImmediatelyInvokedCallableRule' => [['0594']],
		'PHPStan\Rules\PhpDoc\WrongVariableNameInVarTagRule' => [['0595']],
		'PHPStan\Rules\PhpDoc\RequireExtendsDefinitionTraitRule' => [['0596']],
		'PHPStan\Rules\PhpDoc\InvalidPhpDocTagValueRule' => [['0597']],
		'PHPStan\Rules\PhpDoc\MethodAssertRule' => [['0598']],
		'PHPStan\Rules\DateTimeInstantiationRule' => [['0599']],
		'PHPStan\Rules\Whitespace\FileWhitespaceRule' => [['0600']],
		'PHPStan\Rules\Missing\MissingReturnRule' => [['0601']],
		'PHPStan\Rules\Exceptions\CatchWithUnthrownExceptionRule' => [['0602']],
		'PHPStan\Rules\Exceptions\ThrowsVoidFunctionWithExplicitThrowPointRule' => [['0603']],
		'PHPStan\Rules\Exceptions\ThrowExprTypeRule' => [['0604']],
		'PHPStan\Rules\Exceptions\ThrowExpressionRule' => [['0605']],
		'PHPStan\Rules\Exceptions\CaughtExceptionExistenceRule' => [['0606']],
		'PHPStan\Rules\Exceptions\NoncapturingCatchRule' => [['0607']],
		'PHPStan\Rules\Exceptions\ThrowsVoidPropertyHookWithExplicitThrowPointRule' => [['0608']],
		'PHPStan\Rules\Exceptions\ThrowsVoidMethodWithExplicitThrowPointRule' => [['0609']],
		'PHPStan\Rules\Exceptions\OverwrittenExitPointByFinallyRule' => [['0610']],
		'PHPStan\Rules\Arrays\OffsetAccessAssignOpRule' => [['0611']],
		'PHPStan\Rules\Arrays\OffsetAccessAssignmentRule' => [['0612']],
		'PHPStan\Rules\Arrays\ArrayDestructuringRule' => [['0613']],
		'PHPStan\Rules\Arrays\DuplicateKeysInLiteralArraysRule' => [['0614']],
		'PHPStan\Rules\Arrays\ArrayUnpackingRule' => [['0615']],
		'PHPStan\Rules\Arrays\NonexistentOffsetInArrayDimFetchRule' => [['0616']],
		'PHPStan\Rules\Arrays\UnpackIterableInArrayRule' => [['0617']],
		'PHPStan\Rules\Arrays\IterableInForeachRule' => [['0618']],
		'PHPStan\Rules\Arrays\OffsetAccessWithoutDimForReadingRule' => [['0619']],
		'PHPStan\Rules\Arrays\OffsetAccessValueAssignmentRule' => [['0620']],
		'PHPStan\Rules\Arrays\InvalidKeyInArrayDimFetchRule' => [['0621']],
		'PHPStan\Rules\Arrays\DeadForeachRule' => [['0622']],
		'PHPStan\Rules\Arrays\InvalidKeyInArrayItemRule' => [['0623']],
		'PHPStan\Rules\Ignore\IgnoreParseErrorRule' => [['0624']],
		'PHPStan\Rules\Names\UsedNamesRule' => [['0625']],
		'PHPStan\Rules\Variables\ParameterOutExecutionEndTypeRule' => [['0626']],
		'PHPStan\Rules\Variables\InvalidVariableAssignRule' => [['0627']],
		'PHPStan\Rules\Variables\CompactVariablesRule' => [['0628']],
		'PHPStan\Rules\Variables\NullCoalesceRule' => [['0629']],
		'PHPStan\Rules\Variables\UnsetRule' => [['0630']],
		'PHPStan\Rules\Variables\DefinedVariableRule' => [['0631']],
		'PHPStan\Rules\Variables\ThisInGlobalStatementRule' => [['0632']],
		'PHPStan\Rules\Variables\EmptyRule' => [['0633']],
		'PHPStan\Rules\Variables\VariableCloningRule' => [['0634']],
		'PHPStan\Rules\Variables\ParameterOutAssignedTypeRule' => [['0635']],
		'PHPStan\Rules\Variables\IssetRule' => [['0636']],
		'PHPStan\Rules\Variables\ThisInStaticStatementRule' => [['0637']],
		'PHPStan\Rules\Functions\ParameterCastableToStringRule' => [['0638']],
		'PHPStan\Rules\Functions\IncompatibleArrowFunctionDefaultParameterTypeRule' => [['0639']],
		'PHPStan\Rules\Functions\ClosureReturnTypeRule' => [['0640']],
		'PHPStan\Rules\Functions\CallToFunctionStatementWithoutSideEffectsRule' => [['0641']],
		'PHPStan\Rules\Functions\CallCallablesRule' => [['0642']],
		'PHPStan\Rules\Functions\ImplodeParameterCastableToStringRule' => [['0643']],
		'PHPStan\Rules\Functions\ArrowFunctionReturnNullsafeByRefRule' => [['0644']],
		'PHPStan\Rules\Functions\DefineParametersRule' => [['0645']],
		'PHPStan\Rules\Functions\ArrowFunctionReturnTypeRule' => [['0646']],
		'PHPStan\Rules\Functions\CallToNonExistentFunctionRule' => [['0647']],
		'PHPStan\Rules\Functions\ArrayFilterRule' => [['0648']],
		'PHPStan\Rules\Functions\ReturnTypeAfterFinallyRule' => [['0649']],
		'PHPStan\Rules\Functions\IncompatibleDefaultParameterTypeRule' => [['0650']],
		'PHPStan\Rules\Functions\ReturnTypeRule' => [['0651']],
		'PHPStan\Rules\Functions\InvalidParameterNameRule' => [['0652']],
		'PHPStan\Rules\Functions\ExistingClassesInClosureTypehintsRule' => [['0653']],
		'PHPStan\Rules\Functions\ExistingClassesInTypehintsRule' => [['0654']],
		'PHPStan\Rules\Functions\InvalidLexicalVariablesInClosureUseRule' => [['0655']],
		'PHPStan\Rules\Functions\InnerFunctionRule' => [['0656']],
		'PHPStan\Rules\Functions\SortParameterCastableToStringRule' => [['0657']],
		'PHPStan\Rules\Functions\ParamAttributesRule' => [['0658']],
		'PHPStan\Rules\Functions\UnusedClosureUsesRule' => [['0659']],
		'PHPStan\Rules\Functions\ArrayValuesRule' => [['0660']],
		'PHPStan\Rules\Functions\PrintfArrayParametersRule' => [['0661']],
		'PHPStan\Rules\Functions\UselessFunctionReturnValueRule' => [['0662']],
		'PHPStan\Rules\Functions\IncompatibleClosureDefaultParameterTypeRule' => [['0663']],
		'PHPStan\Rules\Functions\ReturnNullsafeByRefRule' => [['0664']],
		'PHPStan\Rules\Functions\CallToFunctionParametersRule' => [['0665']],
		'PHPStan\Rules\Functions\ClosureAttributesRule' => [['0666']],
		'PHPStan\Rules\Functions\ArrowFunctionAttributesRule' => [['0667']],
		'PHPStan\Rules\Functions\FunctionCallableRule' => [['0668']],
		'PHPStan\Rules\Functions\ExistingClassesInArrowFunctionTypehintsRule' => [['0669']],
		'PHPStan\Rules\Functions\VariadicParametersDeclarationRule' => [['0670']],
		'PHPStan\Rules\Functions\PrintfParametersRule' => [['0671']],
		'PHPStan\Rules\Functions\CallToFunctionStatementWithNoDiscardRule' => [['0672']],
		'PHPStan\Rules\Functions\RandomIntParametersRule' => [['0673']],
		'PHPStan\Rules\Functions\FilterVarRule' => [['0674']],
		'PHPStan\Rules\Functions\FunctionAttributesRule' => [['0675']],
		'PHPStan\Rules\Functions\RedefinedParametersRule' => [['0676']],
		'PHPStan\Rules\Functions\CallUserFuncRule' => [['0677']],
		'PHPStan\Rules\Comparison\ImpossibleCheckTypeStaticMethodCallRule' => [['0678']],
		'PHPStan\Rules\Comparison\ConstantLooseComparisonRule' => [['0679']],
		'PHPStan\Rules\Comparison\BooleanOrConstantConditionRule' => [['0680']],
		'PHPStan\Rules\Comparison\DoWhileLoopConstantConditionRule' => [['0681']],
		'PHPStan\Rules\Comparison\ImpossibleCheckTypeMethodCallRule' => [['0682']],
		'PHPStan\Rules\Comparison\ElseIfConstantConditionRule' => [['0683']],
		'PHPStan\Rules\Comparison\ImpossibleCheckTypeFunctionCallRule' => [['0684']],
		'PHPStan\Rules\Comparison\BooleanAndConstantConditionRule' => [['0685']],
		'PHPStan\Rules\Comparison\UsageOfVoidMatchExpressionRule' => [['0686']],
		'PHPStan\Rules\Comparison\NumberComparisonOperatorsConstantConditionRule' => [['0687']],
		'PHPStan\Rules\Comparison\LogicalXorConstantConditionRule' => [['0688']],
		'PHPStan\Rules\Comparison\IfConstantConditionRule' => [['0689']],
		'PHPStan\Rules\Comparison\ConstantConditionInTraitRule' => [['0690']],
		'PHPStan\Rules\Comparison\FunctionCallConstantConditionRule' => [['0691']],
		'PHPStan\Rules\Comparison\MatchExpressionRule' => [['0692']],
		'PHPStan\Rules\Comparison\BooleanNotConstantConditionRule' => [['0693']],
		'PHPStan\Rules\Comparison\StrictComparisonOfDifferentTypesRule' => [['0694']],
		'PHPStan\Rules\Comparison\WhileLoopAlwaysTrueConditionRule' => [['0695']],
		'PHPStan\Rules\Comparison\TernaryOperatorConstantConditionRule' => [['0696']],
		'PHPStan\Rules\Comparison\WhileLoopAlwaysFalseConditionRule' => [['0697']],
		'PHPStan\Rules\Types\InvalidTypesInUnionRule' => [['0698']],
		'PHPStan\Rules\Keywords\DeclareStrictTypesRule' => [['0699']],
		'PHPStan\Rules\Keywords\GotoUndefinedLabelRule' => [['0700']],
		'PHPStan\Rules\Keywords\RequireFileExistsRule' => [['0701']],
		'PHPStan\Rules\Keywords\ContinueBreakInLoopRule' => [['0702']],
		'PHPStan\Rules\Properties\AccessPropertiesRule' => [['0703']],
		'PHPStan\Rules\Properties\DefaultValueTypesAssignedToPropertiesRule' => [['0704']],
		'PHPStan\Rules\Properties\AccessPropertiesInAssignRule' => [['0705']],
		'PHPStan\Rules\Properties\ReadOnlyByPhpDocPropertyAssignRefRule' => [['0706']],
		'PHPStan\Rules\Properties\PropertyAssignRefRule' => [['0707']],
		'PHPStan\Rules\Properties\ReadingWriteOnlyPropertiesRule' => [['0708']],
		'PHPStan\Rules\Properties\ExistingClassesInPropertiesRule' => [['0709']],
		'PHPStan\Rules\Properties\PropertyInClassRule' => [['0710']],
		'PHPStan\Rules\Properties\PropertyHookAttributesRule' => [['0711']],
		'PHPStan\Rules\Properties\ReadOnlyByPhpDocPropertyRule' => [['0712']],
		'PHPStan\Rules\Properties\MissingReadOnlyByPhpDocPropertyAssignRule' => [['0713']],
		'PHPStan\Rules\Properties\PropertiesInInterfaceRule' => [['0714']],
		'PHPStan\Rules\Properties\ReadOnlyByPhpDocPropertyAssignRule' => [['0715']],
		'PHPStan\Rules\Properties\GetNonVirtualPropertyHookReadRule' => [['0716']],
		'PHPStan\Rules\Properties\AccessPrivatePropertyThroughStaticRule' => [['0717']],
		'PHPStan\Rules\Properties\PropertyAttributesRule' => [['0718']],
		'PHPStan\Rules\Properties\SetNonVirtualPropertyHookAssignRule' => [['0719']],
		'PHPStan\Rules\Properties\SetPropertyHookParameterRule' => [['0720']],
		'PHPStan\Rules\Properties\OverridingPropertyRule' => [['0721']],
		'PHPStan\Rules\Properties\ReadOnlyPropertyAssignRule' => [['0722']],
		'PHPStan\Rules\Properties\WritingToReadOnlyPropertiesRule' => [['0723']],
		'PHPStan\Rules\Properties\InvalidCallablePropertyTypeRule' => [['0724']],
		'PHPStan\Rules\Properties\MissingReadOnlyPropertyAssignRule' => [['0725']],
		'PHPStan\Rules\Properties\ReadOnlyPropertyRule' => [['0726']],
		'PHPStan\Rules\Properties\AccessStaticPropertiesInAssignRule' => [['0727']],
		'PHPStan\Rules\Properties\ReadOnlyPropertyAssignRefRule' => [['0728']],
		'PHPStan\Rules\Properties\TypesAssignedToPropertiesRule' => [['0729']],
		'PHPStan\Rules\Properties\ExistingClassesInPropertyHookTypehintsRule' => [['0730']],
		'PHPStan\Rules\Properties\AccessStaticPropertiesRule' => [['0731']],
		'PHPStan\Rules\Properties\NullsafePropertyFetchRule' => [['0732']],
		'PHPStan\Rules\Classes\RequireExtendsRule' => [['0733']],
		'PHPStan\Rules\Classes\AllowedSubTypesRule' => [['0734']],
		'PHPStan\Rules\Classes\TraitAttributeClassRule' => [['0735']],
		'PHPStan\Rules\Classes\EnumSanityRule' => [['0736']],
		'PHPStan\Rules\Classes\InstantiationCallableRule' => [['0737']],
		'PHPStan\Rules\Classes\MethodTagRule' => [['0738']],
		'PHPStan\Rules\Classes\ClassConstantAttributesRule' => [['0739']],
		'PHPStan\Rules\Classes\ExistingClassInTraitUseRule' => [['0740']],
		'PHPStan\Rules\Classes\NewStaticRule' => [['0741']],
		'PHPStan\Rules\Classes\LocalTypeAliasesRule' => [['0742']],
		'PHPStan\Rules\Classes\LocalTypeTraitUseAliasesRule' => [['0743']],
		'PHPStan\Rules\Classes\MixinTraitUseRule' => [['0744']],
		'PHPStan\Rules\Classes\InvalidPromotedPropertiesRule' => [['0745']],
		'PHPStan\Rules\Classes\ExistingClassInInstanceOfRule' => [['0746']],
		'PHPStan\Rules\Classes\LocalTypeTraitAliasesRule' => [['0747']],
		'PHPStan\Rules\Classes\ReadOnlyClassRule' => [['0748']],
		'PHPStan\Rules\Classes\InstantiationRule' => [['0749']],
		'PHPStan\Rules\Classes\DuplicateDeclarationRule' => [['0750']],
		'PHPStan\Rules\Classes\AccessPrivateConstantThroughStaticRule' => [['0751']],
		'PHPStan\Rules\Classes\DuplicateTraitDeclarationRule' => [['0752']],
		'PHPStan\Rules\Classes\PropertyTagRule' => [['0753']],
		'PHPStan\Rules\Classes\NonClassAttributeClassRule' => [['0754']],
		'PHPStan\Rules\Classes\ExistingClassesInInterfaceExtendsRule' => [['0755']],
		'PHPStan\Rules\Classes\MethodTagTraitUseRule' => [['0756']],
		'PHPStan\Rules\Classes\MixinRule' => [['0757']],
		'PHPStan\Rules\Classes\ClassAttributesRule' => [['0758']],
		'PHPStan\Rules\Classes\PropertyTagTraitUseRule' => [['0759']],
		'PHPStan\Rules\Classes\PropertyTagTraitRule' => [['0760']],
		'PHPStan\Rules\Classes\ClassConstantRule' => [['0761']],
		'PHPStan\Rules\Classes\RequireImplementsRule' => [['0762']],
		'PHPStan\Rules\Classes\MixinTraitRule' => [['0763']],
		'PHPStan\Rules\Classes\ImpossibleInstanceOfRule' => [['0764']],
		'PHPStan\Rules\Classes\ExistingClassesInEnumImplementsRule' => [['0765']],
		'PHPStan\Rules\Classes\UnusedConstructorParametersRule' => [['0766']],
		'PHPStan\Rules\Classes\ExistingClassInClassExtendsRule' => [['0767']],
		'PHPStan\Rules\Classes\MethodTagTraitRule' => [['0768']],
		'PHPStan\Rules\Classes\ExistingClassesInClassImplementsRule' => [['0769']],
		'PHPStan\Rules\EnumCases\EnumCaseAttributesRule' => [['0770']],
		'PHPStan\Rules\EnumCases\EnumCaseOutsideEnumRule' => [['0771']],
		'PHPStan\Rules\Traits\ConflictingTraitConstantsRule' => [['0772']],
		'PHPStan\Rules\Traits\ConstantsInTraitsRule' => [['0773']],
		'PHPStan\Rules\Traits\NotAnalysedTraitRule' => [['0774']],
		'PHPStan\Rules\Traits\TraitAttributesRule' => [['0775']],
		'PHPStan\Rules\Regexp\RegularExpressionQuotingRule' => [['0776']],
		'PHPStan\Rules\Regexp\RegularExpressionPatternRule' => [['0777']],
		'PHPStan\Rules\Api\GetTemplateTypeRule' => [['0778']],
		'PHPStan\Rules\Api\OldPhpParser4ClassRule' => [['0779']],
		'PHPStan\Rules\Api\ApiInterfaceExtendsRule' => [['0780']],
		'PHPStan\Rules\Api\NodeConnectingVisitorAttributesRule' => [['0781']],
		'PHPStan\Rules\Api\ApiInstanceofTypeRule' => [['0782']],
		'PHPStan\Rules\Api\ApiInstantiationRule' => [['0783']],
		'PHPStan\Rules\Api\ApiClassConstFetchRule' => [['0784']],
		'PHPStan\Rules\Api\ApiStaticCallRule' => [['0785']],
		'PHPStan\Rules\Api\ApiMethodCallRule' => [['0786']],
		'PHPStan\Rules\Api\ApiInstanceofRule' => [['0787']],
		'PHPStan\Rules\Api\RuntimeReflectionInstantiationRule' => [['0788']],
		'PHPStan\Rules\Api\ApiClassExtendsRule' => [['0789']],
		'PHPStan\Rules\Api\RuntimeReflectionFunctionRule' => [['0790']],
		'PHPStan\Rules\Api\PhpStanNamespaceIn3rdPartyPackageRule' => [['0791']],
		'PHPStan\Rules\Api\ApiClassImplementsRule' => [['0792']],
		'PHPStan\Rules\Api\ApiTraitUseRule' => [['0793']],
		'PHPStan\Rules\Cast\InvalidPartOfEncapsedStringRule' => [['0794']],
		'PHPStan\Rules\Cast\UnsetCastRule' => [['0795']],
		'PHPStan\Rules\Cast\EchoRule' => [['0796']],
		'PHPStan\Rules\Cast\VoidCastRule' => [['0797']],
		'PHPStan\Rules\Cast\PrintRule' => [['0798']],
		'PHPStan\Rules\Cast\InvalidCastRule' => [['0799']],
		'PHPStan\Rules\Cast\DeprecatedCastRule' => [['0800']],
		'PHPStan\Rules\DeadCode\NoopRule' => [['0801']],
		'PHPStan\Rules\DeadCode\UnreachableStatementRule' => [['0802']],
		'PHPStan\Rules\DeadCode\CallToConstructorStatementWithoutImpurePointsRule' => [['0803']],
		'PHPStan\Rules\DeadCode\UnusedPrivateConstantRule' => [['0804']],
		'PHPStan\Rules\DeadCode\UnusedPrivateMethodRule' => [['0805']],
		'PHPStan\Rules\DeadCode\CallToFunctionStatementWithoutImpurePointsRule' => [['0806']],
		'PHPStan\Rules\DeadCode\UnusedPrivatePropertyRule' => [['0807']],
		'PHPStan\Rules\DeadCode\CallToMethodStatementWithoutImpurePointsRule' => [['0808']],
		'PHPStan\Rules\DeadCode\CallToStaticMethodStatementWithoutImpurePointsRule' => [['0809']],
		'PHPStan\Rules\TooWideTypehints\TooWideFunctionReturnTypehintRule' => [['0810']],
		'PHPStan\Rules\TooWideTypehints\TooWideFunctionParameterOutTypeRule' => [['0811']],
		'PHPStan\Rules\TooWideTypehints\TooWideMethodParameterOutTypeRule' => [['0812']],
		'PHPStan\Rules\TooWideTypehints\TooWideArrowFunctionReturnTypehintRule' => [['0813']],
		'PHPStan\Rules\TooWideTypehints\TooWideClosureReturnTypehintRule' => [['0814']],
		'PHPStan\Rules\TooWideTypehints\TooWidePropertyTypeRule' => [['0815']],
		'PHPStan\Rules\TooWideTypehints\TooWideMethodReturnTypehintRule' => [['0816']],
		'PHPStan\Rules\Pure\PurePropertyHookRule' => [['0817']],
		'PHPStan\Rules\Pure\PureFunctionRule' => [['0818']],
		'PHPStan\Rules\Pure\PureMethodRule' => [['0819']],
		'PHPStan\Rules\Constants\ValueAssignedToClassConstantRule' => [['0820']],
		'PHPStan\Rules\Constants\FinalPrivateConstantRule' => [['0821']],
		'PHPStan\Rules\Constants\DynamicClassConstantFetchRule' => [['0822']],
		'PHPStan\Rules\Constants\OverridingConstantRule' => [['0823']],
		'PHPStan\Rules\Constants\FinalConstantRule' => [['0824']],
		'PHPStan\Rules\Constants\ConstantAttributesRule' => [['0825']],
		'PHPStan\Rules\Constants\ConstantRule' => [['0826']],
		'PHPStan\Rules\Constants\NativeTypedClassConstantRule' => [['0827']],
		'PHPStan\Rules\Constants\ClassAsClassConstantRule' => [['0828']],
		'PHPStan\Rules\Constants\MagicConstantContextRule' => [['0829']],
		'PHPStan\Rules\Operators\InvalidIncDecOperationRule' => [['0830']],
		'PHPStan\Rules\Operators\InvalidUnaryOperationRule' => [['0831']],
		'PHPStan\Rules\Operators\PipeOperatorRule' => [['0832']],
		'PHPStan\Rules\Operators\BacktickRule' => [['0833']],
		'PHPStan\Rules\Operators\InvalidAssignVarRule' => [['0834']],
		'PHPStan\Rules\Operators\InvalidComparisonOperationRule' => [['0835']],
		'PHPStan\Rules\Operators\InvalidBinaryOperationRule' => [['0836']],
		'PHPStan\Rules\Methods\ConsistentConstructorDeclarationRule' => [['0837']],
		'PHPStan\Rules\Methods\CallPrivateMethodThroughStaticRule' => [['0838']],
		'PHPStan\Rules\Methods\FinalPrivateMethodRule' => [['0839']],
		'PHPStan\Rules\Methods\MethodCallableRule' => [['0840']],
		'PHPStan\Rules\Methods\CallToMethodStatementWithNoDiscardRule' => [['0841']],
		'PHPStan\Rules\Methods\OverridingMethodRule' => [['0842']],
		'PHPStan\Rules\Methods\CallToConstructorStatementWithoutSideEffectsRule' => [['0843']],
		'PHPStan\Rules\Methods\StaticMethodCallableRule' => [['0844']],
		'PHPStan\Rules\Methods\MethodVisibilityInInterfaceRule' => [['0845']],
		'PHPStan\Rules\Methods\IncompatibleDefaultParameterTypeRule' => [['0846']],
		'PHPStan\Rules\Methods\CallToMethodStatementWithoutSideEffectsRule' => [['0847']],
		'PHPStan\Rules\Methods\ReturnTypeRule' => [['0848']],
		'PHPStan\Rules\Methods\CallStaticMethodsRule' => [['0849']],
		'PHPStan\Rules\Methods\ExistingClassesInTypehintsRule' => [['0850']],
		'PHPStan\Rules\Methods\MissingMethodImplementationRule' => [['0851']],
		'PHPStan\Rules\Methods\NullsafeMethodCallRule' => [['0852']],
		'PHPStan\Rules\Methods\AbstractPrivateMethodRule' => [['0853']],
		'PHPStan\Rules\Methods\AbstractMethodInNonAbstractClassRule' => [['0854']],
		'PHPStan\Rules\Methods\ConsistentConstructorRule' => [['0855']],
		'PHPStan\Rules\Methods\ConstructorReturnTypeRule' => [['0856']],
		'PHPStan\Rules\Methods\CallMethodsRule' => [['0857']],
		'PHPStan\Rules\Methods\CallToStaticMethodStatementWithNoDiscardRule' => [['0858']],
		'PHPStan\Rules\Methods\MethodAttributesRule' => [['0859']],
		'PHPStan\Rules\Methods\CallToStaticMethodStatementWithoutSideEffectsRule' => [['0860']],
		'PHPStan\Rules\Methods\MethodCallWithPossiblyRenamedNamedArgumentRule' => [['0861']],
		'PHPStan\Rules\Methods\MissingMagicSerializationMethodsRule' => [['0862']],
		'PHPStan\Rules\Generics\ClassTemplateTypeRule' => [['0863']],
		'PHPStan\Rules\Generics\MethodTemplateTypeRule' => [['0864']],
		'PHPStan\Rules\Generics\FunctionTemplateTypeRule' => [['0865']],
		'PHPStan\Rules\Generics\MethodSignatureVarianceRule' => [['0866']],
		'PHPStan\Rules\Generics\UsedTraitsRule' => [['0867']],
		'PHPStan\Rules\Generics\FunctionSignatureVarianceRule' => [['0868']],
		'PHPStan\Rules\Generics\PropertyVarianceRule' => [['0869']],
		'PHPStan\Rules\Generics\EnumAncestorsRule' => [['0870']],
		'PHPStan\Rules\Generics\MethodTagTemplateTypeRule' => [['0871']],
		'PHPStan\Rules\Generics\InterfaceAncestorsRule' => [['0872']],
		'PHPStan\Rules\Generics\TraitTemplateTypeRule' => [['0873']],
		'PHPStan\Rules\Generics\ClassAncestorsRule' => [['0874']],
		'PHPStan\Rules\Generics\MethodTagTemplateTypeTraitRule' => [['0875']],
		'PHPStan\Rules\Generics\InterfaceTemplateTypeRule' => [['0876']],
		'PHPStan\Rules\Generics\EnumTemplateTypeRule' => [['0877']],
		'PHPStan\Collectors\Collector' => [
			['01027', '01028', '01029', '01030', '01031', '01032', '01033', '01038', '01039', '01040'],
			['0878', '0879', '0880', '0881', '0882', '0883', '0884', '0885', '0886'],
		],
		'PHPStan\Rules\Traits\TraitUseCollector' => [['0878']],
		'PHPStan\Rules\Traits\TraitDeclarationCollector' => [['0879']],
		'PHPStan\Rules\DeadCode\PossiblyPureFuncCallCollector' => [['0880']],
		'PHPStan\Rules\DeadCode\ConstructorWithoutImpurePointsCollector' => [['0881']],
		'PHPStan\Rules\DeadCode\PossiblyPureStaticCallCollector' => [['0882']],
		'PHPStan\Rules\DeadCode\FunctionWithoutImpurePointsCollector' => [['0883']],
		'PHPStan\Rules\DeadCode\PossiblyPureMethodCallCollector' => [['0884']],
		'PHPStan\Rules\DeadCode\MethodWithoutImpurePointsCollector' => [['0885']],
		'PHPStan\Rules\DeadCode\PossiblyPureNewCollector' => [['0886']],
		'PHPStan\DependencyInjection\ExtensionsCollection' => [
			2 => [
				'phpstan.extensionsCollection.PhpParser.NodeVisitor',
				'phpstan.extensionsCollection.PHPStan.PhpDoc.StubFilesExtension',
				'phpstan.extensionsCollection.PHPStan.PhpDoc.TypeNodeResolverExtension',
				'phpstan.extensionsCollection.PHPStan.Reflection.PropertiesClassReflectionExtension',
				'phpstan.extensionsCollection.PHPStan.Reflection.AdditionalConstructorsExtension',
				'phpstan.extensionsCollection.PHPStan.Reflection.MethodsClassReflectionExtension',
				'phpstan.extensionsCollection.PHPStan.Reflection.AllowedSubTypesClassReflectionExtension',
				'phpstan.extensionsCollection.PHPStan.Reflection.Deprecation.ClassDeprecationExtension',
				'phpstan.extensionsCollection.PHPStan.Reflection.Deprecation.EnumCaseDeprecationExtension',
				'phpstan.extensionsCollection.PHPStan.Reflection.Deprecation.FunctionDeprecationExtension',
				'phpstan.extensionsCollection.PHPStan.Reflection.Deprecation.ConstantDeprecationExtension',
				'phpstan.extensionsCollection.PHPStan.Reflection.Deprecation.PropertyDeprecationExtension',
				'phpstan.extensionsCollection.PHPStan.Reflection.Deprecation.MethodDeprecationExtension',
				'phpstan.extensionsCollection.PHPStan.Reflection.Deprecation.ClassConstantDeprecationExtension',
				'phpstan.extensionsCollection.PHPStan.Diagnose.DiagnoseExtension',
				'phpstan.extensionsCollection.PHPStan.Rules.Properties.ReadWritePropertiesExtension',
				'phpstan.extensionsCollection.PHPStan.Rules.Rule',
				'phpstan.extensionsCollection.PHPStan.Rules.RestrictedUsage.RestrictedClassConstantUsageExtension',
				'phpstan.extensionsCollection.PHPStan.Rules.RestrictedUsage.RestrictedPropertyUsageExtension',
				'phpstan.extensionsCollection.PHPStan.Rules.RestrictedUsage.RestrictedMethodUsageExtension',
				'phpstan.extensionsCollection.PHPStan.Rules.RestrictedUsage.RestrictedFunctionUsageExtension',
				'phpstan.extensionsCollection.PHPStan.Rules.RestrictedUsage.RestrictedClassNameUsageExtension',
				'phpstan.extensionsCollection.PHPStan.Rules.Constants.AlwaysUsedClassConstantsExtension',
				'phpstan.extensionsCollection.PHPStan.Rules.Methods.AlwaysUsedMethodExtension',
				'phpstan.extensionsCollection.PHPStan.Classes.ForbiddenClassNameExtension',
				'phpstan.extensionsCollection.PHPStan.Analyser.ExprHandler',
				'phpstan.extensionsCollection.PHPStan.Analyser.StmtHandler',
				'phpstan.extensionsCollection.PHPStan.Analyser.IgnoreErrorExtension',
				'phpstan.extensionsCollection.PHPStan.Analyser.PerFileAnalysisResettable',
				'phpstan.extensionsCollection.PHPStan.Analyser.ResultCache.ResultCacheMetaExtension',
				'phpstan.extensionsCollection.PHPStan.Analyser.ResultCache.ResultCacheValueExtension',
				'phpstan.extensionsCollection.PHPStan.Collectors.Collector',
				'phpstan.extensionsCollection.PHPStan.Type.FunctionTypeSpecifyingExtension',
				'phpstan.extensionsCollection.PHPStan.Type.MethodTypeSpecifyingExtension',
				'phpstan.extensionsCollection.PHPStan.Type.MethodParameterClosureTypeExtension',
				'phpstan.extensionsCollection.PHPStan.Type.StaticMethodParameterOutTypeExtension',
				'phpstan.extensionsCollection.PHPStan.Type.StaticMethodParameterClosureTypeExtension',
				'phpstan.extensionsCollection.PHPStan.Type.DynamicStaticMethodReturnTypeExtension',
				'phpstan.extensionsCollection.PHPStan.Type.StaticMethodParameterClosureThisExtension',
				'phpstan.extensionsCollection.PHPStan.Type.FunctionParameterClosureTypeExtension',
				'phpstan.extensionsCollection.PHPStan.Type.FunctionParameterClosureThisExtension',
				'phpstan.extensionsCollection.PHPStan.Type.StaticMethodTypeSpecifyingExtension',
				'phpstan.extensionsCollection.PHPStan.Type.FunctionParameterOutTypeExtension',
				'phpstan.extensionsCollection.PHPStan.Type.MethodParameterOutTypeExtension',
				'phpstan.extensionsCollection.PHPStan.Type.UnaryOperatorTypeSpecifyingExtension',
				'phpstan.extensionsCollection.PHPStan.Type.ExpressionTypeResolverExtension',
				'phpstan.extensionsCollection.PHPStan.Type.MethodParameterClosureThisExtension',
				'phpstan.extensionsCollection.PHPStan.Type.DynamicMethodReturnTypeExtension',
				'phpstan.extensionsCollection.PHPStan.Type.OperatorTypeSpecifyingExtension',
				'phpstan.extensionsCollection.PHPStan.Type.DynamicStaticMethodThrowTypeExtension',
				'phpstan.extensionsCollection.PHPStan.Type.DynamicFunctionThrowTypeExtension',
				'phpstan.extensionsCollection.PHPStan.Type.DynamicFunctionReturnTypeExtension',
				'phpstan.extensionsCollection.PHPStan.Type.DynamicMethodThrowTypeExtension',
			],
		],
		'PHPStan\DependencyInjection\LazyExtensionsCollection' => [
			2 => [
				'phpstan.extensionsCollection.PhpParser.NodeVisitor',
				'phpstan.extensionsCollection.PHPStan.PhpDoc.StubFilesExtension',
				'phpstan.extensionsCollection.PHPStan.PhpDoc.TypeNodeResolverExtension',
				'phpstan.extensionsCollection.PHPStan.Reflection.PropertiesClassReflectionExtension',
				'phpstan.extensionsCollection.PHPStan.Reflection.AdditionalConstructorsExtension',
				'phpstan.extensionsCollection.PHPStan.Reflection.MethodsClassReflectionExtension',
				'phpstan.extensionsCollection.PHPStan.Reflection.AllowedSubTypesClassReflectionExtension',
				'phpstan.extensionsCollection.PHPStan.Reflection.Deprecation.ClassDeprecationExtension',
				'phpstan.extensionsCollection.PHPStan.Reflection.Deprecation.EnumCaseDeprecationExtension',
				'phpstan.extensionsCollection.PHPStan.Reflection.Deprecation.FunctionDeprecationExtension',
				'phpstan.extensionsCollection.PHPStan.Reflection.Deprecation.ConstantDeprecationExtension',
				'phpstan.extensionsCollection.PHPStan.Reflection.Deprecation.PropertyDeprecationExtension',
				'phpstan.extensionsCollection.PHPStan.Reflection.Deprecation.MethodDeprecationExtension',
				'phpstan.extensionsCollection.PHPStan.Reflection.Deprecation.ClassConstantDeprecationExtension',
				'phpstan.extensionsCollection.PHPStan.Diagnose.DiagnoseExtension',
				'phpstan.extensionsCollection.PHPStan.Rules.Properties.ReadWritePropertiesExtension',
				'phpstan.extensionsCollection.PHPStan.Rules.Rule',
				'phpstan.extensionsCollection.PHPStan.Rules.RestrictedUsage.RestrictedClassConstantUsageExtension',
				'phpstan.extensionsCollection.PHPStan.Rules.RestrictedUsage.RestrictedPropertyUsageExtension',
				'phpstan.extensionsCollection.PHPStan.Rules.RestrictedUsage.RestrictedMethodUsageExtension',
				'phpstan.extensionsCollection.PHPStan.Rules.RestrictedUsage.RestrictedFunctionUsageExtension',
				'phpstan.extensionsCollection.PHPStan.Rules.RestrictedUsage.RestrictedClassNameUsageExtension',
				'phpstan.extensionsCollection.PHPStan.Rules.Constants.AlwaysUsedClassConstantsExtension',
				'phpstan.extensionsCollection.PHPStan.Rules.Methods.AlwaysUsedMethodExtension',
				'phpstan.extensionsCollection.PHPStan.Classes.ForbiddenClassNameExtension',
				'phpstan.extensionsCollection.PHPStan.Analyser.ExprHandler',
				'phpstan.extensionsCollection.PHPStan.Analyser.StmtHandler',
				'phpstan.extensionsCollection.PHPStan.Analyser.IgnoreErrorExtension',
				'phpstan.extensionsCollection.PHPStan.Analyser.PerFileAnalysisResettable',
				'phpstan.extensionsCollection.PHPStan.Analyser.ResultCache.ResultCacheMetaExtension',
				'phpstan.extensionsCollection.PHPStan.Analyser.ResultCache.ResultCacheValueExtension',
				'phpstan.extensionsCollection.PHPStan.Collectors.Collector',
				'phpstan.extensionsCollection.PHPStan.Type.FunctionTypeSpecifyingExtension',
				'phpstan.extensionsCollection.PHPStan.Type.MethodTypeSpecifyingExtension',
				'phpstan.extensionsCollection.PHPStan.Type.MethodParameterClosureTypeExtension',
				'phpstan.extensionsCollection.PHPStan.Type.StaticMethodParameterOutTypeExtension',
				'phpstan.extensionsCollection.PHPStan.Type.StaticMethodParameterClosureTypeExtension',
				'phpstan.extensionsCollection.PHPStan.Type.DynamicStaticMethodReturnTypeExtension',
				'phpstan.extensionsCollection.PHPStan.Type.StaticMethodParameterClosureThisExtension',
				'phpstan.extensionsCollection.PHPStan.Type.FunctionParameterClosureTypeExtension',
				'phpstan.extensionsCollection.PHPStan.Type.FunctionParameterClosureThisExtension',
				'phpstan.extensionsCollection.PHPStan.Type.StaticMethodTypeSpecifyingExtension',
				'phpstan.extensionsCollection.PHPStan.Type.FunctionParameterOutTypeExtension',
				'phpstan.extensionsCollection.PHPStan.Type.MethodParameterOutTypeExtension',
				'phpstan.extensionsCollection.PHPStan.Type.UnaryOperatorTypeSpecifyingExtension',
				'phpstan.extensionsCollection.PHPStan.Type.ExpressionTypeResolverExtension',
				'phpstan.extensionsCollection.PHPStan.Type.MethodParameterClosureThisExtension',
				'phpstan.extensionsCollection.PHPStan.Type.DynamicMethodReturnTypeExtension',
				'phpstan.extensionsCollection.PHPStan.Type.OperatorTypeSpecifyingExtension',
				'phpstan.extensionsCollection.PHPStan.Type.DynamicStaticMethodThrowTypeExtension',
				'phpstan.extensionsCollection.PHPStan.Type.DynamicFunctionThrowTypeExtension',
				'phpstan.extensionsCollection.PHPStan.Type.DynamicFunctionReturnTypeExtension',
				'phpstan.extensionsCollection.PHPStan.Type.DynamicMethodThrowTypeExtension',
			],
		],
		'Larastan\Larastan\Rules\UselessConstructs\NoUselessWithFunctionCallsRule' => [['rules.0']],
		'Larastan\Larastan\Rules\UselessConstructs\NoUselessValueFunctionCallsRule' => [['rules.1']],
		'Larastan\Larastan\Rules\DeferrableServiceProviderMissingProvidesRule' => [['rules.2']],
		'Larastan\Larastan\Rules\ConsoleCommand\UndefinedArgumentOrOptionRule' => [['rules.3']],
		'PhpParser\BuilderFactory' => [['0887']],
		'PhpParser\NodeVisitor\NameResolver' => [['0888']],
		'PHPStan\PhpDocParser\ParserConfig' => [['0889']],
		'PHPStan\PhpDocParser\Lexer\Lexer' => [['0890']],
		'PHPStan\PhpDocParser\Parser\TypeParser' => [['0891']],
		'PHPStan\PhpDocParser\Parser\ConstExprParser' => [['0892']],
		'PHPStan\PhpDocParser\Parser\PhpDocParser' => [['0893']],
		'PHPStan\PhpDocParser\Printer\Printer' => [['0894']],
		'PHPStan\BetterReflection\SourceLocator\SourceStubber\SourceStubber' => [1 => ['0895', '0896']],
		'PHPStan\BetterReflection\SourceLocator\SourceStubber\PhpStormStubsSourceStubber' => [['0895']],
		'PHPStan\BetterReflection\SourceLocator\SourceStubber\ReflectionSourceStubber' => [['0896']],
		'PHPStan\Type\Php\ReflectionGetAttributesMethodReturnTypeExtension' => [['0897', '0898', '0899', '0900', '0901']],
		'PHPStan\Type\Php\DateTimeModifyReturnTypeExtension' => [['0902', '0903']],
		'PHPStan\Reflection\PHPStan\NativeReflectionEnumReturnDynamicReturnTypeExtension' => [['0904', '0905']],
		'PHPStan\Reflection\BetterReflection\Type\AdapterReflectionEnumCaseDynamicReturnTypeExtension' => [
			['0906', '0907'],
		],
		'PHPStan\Command\ErrorFormatter\JsonErrorFormatter' => [['errorFormatter.json', 'errorFormatter.prettyJson']],
		'PHPStan\File\FileExcluder' => [2 => ['fileExcluderAnalyse', 'fileExcluderScan']],
		'PHPStan\File\FileFinder' => [2 => ['fileFinderAnalyse', 'fileFinderScan']],
		'PHPStan\Cache\CacheStorage' => [2 => ['cacheStorage']],
		'PHPStan\Cache\FileCacheStorage' => [2 => ['cacheStorage']],
		'PHPStan\BetterReflection\SourceLocator\Type\SourceLocator' => [2 => ['betterReflectionSourceLocator']],
		'PHPStan\Parser\Parser' => [
			2 => [
				'php8Parser',
				'currentPhpVersionSimpleDirectParser',
				'currentPhpVersionSimpleParser',
				'currentPhpVersionRichParser',
				'pathRoutingParser',
				'cachedRichParser',
				'cachedSimpleParser',
				'defaultAnalysisParser',
				'freshStubParser',
				'stubParser',
				'migrationsParser',
			],
		],
		'PHPStan\Parser\SimpleParser' => [2 => ['php8Parser', 'currentPhpVersionSimpleDirectParser']],
		'PhpParser\Lexer' => [2 => ['php8Lexer', 'currentPhpVersionLexer']],
		'PhpParser\Lexer\Emulative' => [2 => ['php8Lexer']],
		'PhpParser\ParserAbstract' => [2 => ['php8PhpParser', 'currentPhpVersionPhpParser']],
		'PhpParser\Parser' => [2 => ['php8PhpParser', 'currentPhpVersionPhpParser', 'phpParserDecorator']],
		'PhpParser\Parser\Php8' => [2 => ['php8PhpParser']],
		'PHPStan\Parser\PhpParserFactory' => [2 => ['currentPhpVersionPhpParserFactory']],
		'PHPStan\Parser\CleaningParser' => [2 => ['currentPhpVersionSimpleParser']],
		'PHPStan\Parser\RichParser' => [2 => ['currentPhpVersionRichParser']],
		'PHPStan\Parser\PathRoutingParser' => [2 => ['pathRoutingParser', 'defaultAnalysisParser']],
		'PHPStan\Parser\PhpParserDecorator' => [2 => ['phpParserDecorator']],
		'PHPStan\Parser\CachedParser' => [
			2 => ['cachedRichParser', 'cachedSimpleParser', 'stubParser', 'migrationsParser'],
		],
		'PHPStan\Parser\StubParser' => [2 => ['freshStubParser']],
		'PHPStan\Rules\Exceptions\MissingCheckedExceptionInFunctionThrowsRule' => [['0908']],
		'PHPStan\Rules\Exceptions\MissingCheckedExceptionInMethodThrowsRule' => [['0909']],
		'PHPStan\Rules\Exceptions\MissingCheckedExceptionInPropertyHookThrowsRule' => [['0910']],
		'PHPStan\Rules\Properties\UninitializedPropertyRule' => [['0911']],
		'PHPStan\Rules\Exceptions\MethodThrowTypeCovarianceRule' => [['0912']],
		'PHPStan\Rules\Classes\NewStaticInAbstractClassStaticMethodRule' => [['0913']],
		'PHPStan\Rules\RestrictedUsage\RestrictedClassConstantUsageExtension' => [['0914']],
		'PHPStan\Rules\InternalTag\RestrictedInternalClassConstantUsageExtension' => [['0914']],
		'PHPStan\Rules\RestrictedUsage\RestrictedClassNameUsageExtension' => [['0915']],
		'PHPStan\Rules\InternalTag\RestrictedInternalClassNameUsageExtension' => [['0915']],
		'PHPStan\Rules\RestrictedUsage\RestrictedFunctionUsageExtension' => [['0916']],
		'PHPStan\Rules\InternalTag\RestrictedInternalFunctionUsageExtension' => [['0916']],
		'PHPStan\Rules\Variables\AssignToByRefExprFromForeachRule' => [['0917']],
		'PHPStan\Rules\RestrictedUsage\RestrictedPropertyUsageExtension' => [['0918']],
		'PHPStan\Rules\InternalTag\RestrictedInternalPropertyUsageExtension' => [['0918']],
		'PHPStan\Rules\RestrictedUsage\RestrictedMethodUsageExtension' => [['0919']],
		'PHPStan\Rules\InternalTag\RestrictedInternalMethodUsageExtension' => [['0919']],
		'PHPStan\Rules\Constants\ValueAssignedToDefineRule' => [['0920']],
		'PHPStan\Rules\Constants\ValueAssignedToGlobalConstantRule' => [['0921']],
		'PHPStan\Rules\Exceptions\TooWideFunctionThrowTypeRule' => [['0922']],
		'PHPStan\Rules\Exceptions\TooWideMethodThrowTypeRule' => [['0923']],
		'PHPStan\Rules\Exceptions\TooWidePropertyHookThrowTypeRule' => [['0924']],
		'PHPStan\Rules\Keywords\UnusedLabelRule' => [['0925']],
		'PHPStan\Rules\Comparison\ImpossibleInArrayHaystackFiniteTypesRule' => [['0926']],
		'PHPStan\Rules\Comparison\SwitchConditionRule' => [['0927']],
		'PHPStan\Rules\DeadCode\UnusedVariableRule' => [['0928']],
		'PHPStan\Rules\Functions\UnusedFunctionParametersRule' => [['0929']],
		'PHPStan\Rules\Methods\UnusedMethodParametersRule' => [['0930']],
		'PHPStan\Rules\Functions\ParameterCastableToNumberRule' => [['0931']],
		'PHPStan\Rules\Functions\PrintfParameterTypeRule' => [['0932']],
		'PHPStan\Rules\DateIntervalInstantiationRule' => [['0933']],
		'PHPStan\Rules\Functions\SortWithoutEffectRule' => [['0934']],
		'Larastan\Larastan\Methods\RelationForwardsCallsExtension' => [['0935']],
		'Larastan\Larastan\Methods\ModelForwardsCallsExtension' => [['0936']],
		'Larastan\Larastan\Methods\EloquentBuilderForwardsCallsExtension' => [['0937']],
		'Larastan\Larastan\Methods\HigherOrderTapProxyExtension' => [['0938']],
		'Larastan\Larastan\Methods\HigherOrderCollectionProxyExtension' => [['0939']],
		'Larastan\Larastan\Methods\StorageMethodsClassReflectionExtension' => [['0940']],
		'Larastan\Larastan\Methods\ContractsMethodsExtension' => [['0941']],
		'Larastan\Larastan\Methods\FacadesMethodsExtension' => [['0942']],
		'Larastan\Larastan\Methods\ManagersMethodsExtension' => [['0943']],
		'Larastan\Larastan\Methods\AuthsMethodsExtension' => [['0944']],
		'Larastan\Larastan\Methods\ModelFactoryMethodsClassReflectionExtension' => [['0945']],
		'Larastan\Larastan\Methods\RedirectResponseMethodsClassReflectionExtension' => [['0946']],
		'Larastan\Larastan\Methods\MacroMethodsClassReflectionExtension' => [['0947']],
		'Larastan\Larastan\Methods\ViewWithMethodsClassReflectionExtension' => [['0948']],
		'Larastan\Larastan\Properties\ModelAccessorExtension' => [['0949']],
		'Larastan\Larastan\Properties\ModelPropertyExtension' => [['0950']],
		'Larastan\Larastan\Properties\HigherOrderCollectionProxyPropertyExtension' => [['0951']],
		'Larastan\Larastan\ReturnTypes\HigherOrderTapProxyExtension' => [['0952']],
		'Larastan\Larastan\ReturnTypes\ContainerArrayAccessDynamicMethodReturnTypeExtension' => [
			['0953', '0954', '0955', '0956'],
		],
		'Larastan\Larastan\Properties\ModelRelationsExtension' => [['0957']],
		'Larastan\Larastan\ReturnTypes\ModelSerializationDynamicMethodReturnTypeExtension' => [['0958']],
		'Larastan\Larastan\ReturnTypes\ModelOnlyDynamicMethodReturnTypeExtension' => [['0959']],
		'Larastan\Larastan\ReturnTypes\ModelFactoryDynamicStaticMethodReturnTypeExtension' => [['0960']],
		'Larastan\Larastan\ReturnTypes\ModelDynamicStaticMethodReturnTypeExtension' => [['0961']],
		'Larastan\Larastan\ReturnTypes\AppMakeDynamicReturnTypeExtension' => [['0962']],
		'Larastan\Larastan\ReturnTypes\AuthExtension' => [['0963']],
		'Larastan\Larastan\ReturnTypes\GuardDynamicStaticMethodReturnTypeExtension' => [['0964']],
		'Larastan\Larastan\ReturnTypes\AuthManagerExtension' => [['0965']],
		'Larastan\Larastan\ReturnTypes\DateExtension' => [['0966']],
		'Larastan\Larastan\ReturnTypes\GuardExtension' => [['0967']],
		'Larastan\Larastan\ReturnTypes\RequestFileExtension' => [['0968']],
		'Larastan\Larastan\ReturnTypes\RequestRouteExtension' => [['0969']],
		'Larastan\Larastan\ReturnTypes\RequestUserExtension' => [['0970']],
		'Larastan\Larastan\ReturnTypes\EloquentBuilderExtension' => [['0971']],
		'Larastan\Larastan\ReturnTypes\RelationCollectionExtension' => [['0972']],
		'Larastan\Larastan\ReturnTypes\TestCaseExtension' => [['0973']],
		'Larastan\Larastan\Support\CollectionHelper' => [['0974']],
		'Larastan\Larastan\ReturnTypes\Helpers\AuthExtension' => [['0975']],
		'Larastan\Larastan\ReturnTypes\Helpers\CollectExtension' => [['0976']],
		'Larastan\Larastan\ReturnTypes\Helpers\NowAndTodayExtension' => [['0977']],
		'Larastan\Larastan\ReturnTypes\Helpers\ResponseExtension' => [['0978']],
		'Larastan\Larastan\ReturnTypes\Helpers\ValidatorExtension' => [['0979']],
		'Larastan\Larastan\ReturnTypes\Helpers\LiteralExtension' => [['0980']],
		'Larastan\Larastan\ReturnTypes\CollectionFilterRejectDynamicReturnTypeExtension' => [['0981']],
		'Larastan\Larastan\ReturnTypes\CollectionWhereNotNullDynamicReturnTypeExtension' => [['0982']],
		'Larastan\Larastan\ReturnTypes\FactoryDynamicMethodReturnTypeExtension' => [['0983']],
		'Larastan\Larastan\Types\AbortIfFunctionTypeSpecifyingExtension' => [['0984', '0985', '0986', '0987']],
		'Larastan\Larastan\ReturnTypes\Helpers\AppExtension' => [['0988']],
		'Larastan\Larastan\ReturnTypes\Helpers\ValueExtension' => [['0989']],
		'Larastan\Larastan\ReturnTypes\Helpers\StrExtension' => [['0990']],
		'Larastan\Larastan\ReturnTypes\Helpers\TapExtension' => [['0991']],
		'Larastan\Larastan\ReturnTypes\StorageDynamicStaticMethodReturnTypeExtension' => [['0992']],
		'PHPStan\PhpDoc\TypeNodeResolverExtension' => [['0993', '0994', '01003', '01007', '01008', '01010', '01011']],
		'Larastan\Larastan\Types\GenericEloquentCollectionTypeNodeResolverExtension' => [['0993']],
		'Larastan\Larastan\Types\ViewStringTypeNodeResolverExtension' => [['0994']],
		'Larastan\Larastan\Rules\OctaneCompatibilityRule' => [['0995']],
		'Larastan\Larastan\Rules\NoEnvCallsOutsideOfConfigRule' => [['0996']],
		'Larastan\Larastan\Rules\NoModelMakeRule' => [['0997']],
		'Larastan\Larastan\Rules\NoImplicitQueryBuilderCallRule' => [['0998']],
		'Larastan\Larastan\Rules\NoUnnecessaryCollectionCallRule' => [['0999']],
		'Larastan\Larastan\Rules\NoUnnecessaryEnumerableToArrayCallsRule' => [['01000']],
		'Larastan\Larastan\Rules\ModelAppendsRule' => [['01001']],
		'Larastan\Larastan\Rules\NoPublicModelScopeAndAccessorRule' => [['01002']],
		'Larastan\Larastan\Types\GenericEloquentBuilderTypeNodeResolverExtension' => [['01003']],
		'Larastan\Larastan\ReturnTypes\AppEnvironmentReturnTypeExtension' => [['01004', '01005']],
		'Larastan\Larastan\ReturnTypes\AppFacadeEnvironmentReturnTypeExtension' => [['01006']],
		'Larastan\Larastan\Types\ModelProperty\ModelPropertyTypeNodeResolverExtension' => [['01007']],
		'PHPStan\PhpDoc\TypeNodeResolverAwareExtension' => [['01008', '01010', '01011']],
		'Larastan\Larastan\Types\CollectionOf\CollectionOfTypeNodeResolverExtension' => [['01008']],
		'PHPStan\Type\MethodParameterClosureTypeExtension' => [['01009']],
		'PHPStan\Type\StaticMethodParameterClosureTypeExtension' => [['01009']],
		'Larastan\Larastan\ClosureTypes\RelationshipQueryCallbackExtension' => [['01009']],
		'Larastan\Larastan\Types\BuilderOf\BuilderOfTypeNodeResolverExtension' => [['01010']],
		'Larastan\Larastan\Types\RelationOf\RelationOfTypeNodeResolverExtension' => [['01011']],
		'Larastan\Larastan\Properties\MigrationHelper' => [['01012']],
		'Larastan\Larastan\SQL\SqlParser' => [0 => ['sqlParser'], 2 => ['iamcalSqlParser']],
		'Larastan\Larastan\SQL\IamcalSqlParser' => [2 => ['iamcalSqlParser']],
		'Larastan\Larastan\SQL\SqlParserFactory' => [['sqlParserFactory']],
		'Larastan\Larastan\Properties\SquashedMigrationHelper' => [['01013']],
		'Larastan\Larastan\Properties\ModelCastHelper' => [['01014']],
		'Larastan\Larastan\Properties\MigrationCache' => [['01015']],
		'Larastan\Larastan\Properties\ModelPropertyHelper' => [['01016']],
		'Larastan\Larastan\Rules\ModelRuleHelper' => [['01017']],
		'Larastan\Larastan\Methods\BuilderHelper' => [['01018']],
		'Larastan\Larastan\Rules\ModelRelationDefaultsRule' => [['01019']],
		'Larastan\Larastan\Rules\RelationExistenceHelper' => [['01020']],
		'Larastan\Larastan\Rules\RelationExistenceRule' => [['01021']],
		'Larastan\Larastan\Rules\CheckDispatchArgumentTypesCompatibleWithClassConstructorRule' => [['01022', '01023']],
		'Larastan\Larastan\Properties\Schema\MySqlDataTypeToPhpTypeConverter' => [['01024']],
		'Larastan\Larastan\LarastanStubFilesExtension' => [['01025']],
		'Larastan\Larastan\Rules\UnusedViewsRule' => [['01026']],
		'Larastan\Larastan\Collectors\UsedViewFunctionCollector' => [['01027']],
		'Larastan\Larastan\Collectors\UsedEmailViewCollector' => [['01028']],
		'Larastan\Larastan\Collectors\UsedEmailAlternativeSyntaxViewCollector' => [['01029']],
		'Larastan\Larastan\Collectors\UsedEmailSendViewCollector' => [['01030']],
		'Larastan\Larastan\Collectors\UsedViewMakeCollector' => [['01031']],
		'Larastan\Larastan\Collectors\UsedViewFacadeMakeCollector' => [['01032']],
		'Larastan\Larastan\Collectors\UsedRouteFacadeViewCollector' => [['01033']],
		'Larastan\Larastan\Collectors\UsedViewInAnotherViewCollector' => [['01034']],
		'Larastan\Larastan\Support\ViewFileHelper' => [['01035']],
		'Larastan\Larastan\Support\ViewParser' => [['01036']],
		'Larastan\Larastan\Rules\NoMissingTranslationsRule' => [['01037']],
		'Larastan\Larastan\Collectors\UsedTranslationFunctionCollector' => [['01038']],
		'Larastan\Larastan\Collectors\UsedTranslationTranslatorCollector' => [['01039']],
		'Larastan\Larastan\Collectors\UsedTranslationFacadeCollector' => [['01040']],
		'Larastan\Larastan\Collectors\UsedTranslationViewCollector' => [['01041']],
		'Larastan\Larastan\ReturnTypes\ApplicationMakeDynamicReturnTypeExtension' => [['01042']],
		'Larastan\Larastan\ReturnTypes\ContainerMakeDynamicReturnTypeExtension' => [['01043']],
		'Larastan\Larastan\ReturnTypes\ConsoleCommand\ArgumentDynamicReturnTypeExtension' => [['01044']],
		'Larastan\Larastan\ReturnTypes\ConsoleCommand\HasArgumentDynamicReturnTypeExtension' => [['01045']],
		'Larastan\Larastan\ReturnTypes\ConsoleCommand\OptionDynamicReturnTypeExtension' => [['01046']],
		'Larastan\Larastan\ReturnTypes\ConsoleCommand\HasOptionDynamicReturnTypeExtension' => [['01047']],
		'Larastan\Larastan\ReturnTypes\TranslatorGetReturnTypeExtension' => [['01048']],
		'Larastan\Larastan\ReturnTypes\LangGetReturnTypeExtension' => [['01049']],
		'Larastan\Larastan\ReturnTypes\TransHelperReturnTypeExtension' => [['01050']],
		'Larastan\Larastan\ReturnTypes\DoubleUnderscoreHelperReturnTypeExtension' => [['01051']],
		'Larastan\Larastan\ReturnTypes\AppMakeHelper' => [['01052']],
		'Larastan\Larastan\Internal\ConsoleApplicationResolver' => [['01053']],
		'Larastan\Larastan\Internal\ConsoleApplicationHelper' => [['01054']],
		'Larastan\Larastan\Support\HigherOrderCollectionProxyHelper' => [['01055']],
		'Larastan\Larastan\ReturnTypes\Helpers\ConfigFunctionDynamicFunctionReturnTypeExtension' => [['01056']],
		'Larastan\Larastan\ReturnTypes\ConfigRepositoryDynamicMethodReturnTypeExtension' => [['01057']],
		'Larastan\Larastan\ReturnTypes\ConfigFacadeCollectionDynamicStaticMethodReturnTypeExtension' => [['01058']],
		'Larastan\Larastan\Support\ConfigParser' => [['01059']],
		'Larastan\Larastan\Internal\ConfigHelper' => [['01060']],
		'Larastan\Larastan\ReturnTypes\Helpers\EnvFunctionDynamicFunctionReturnTypeExtension' => [['01061']],
		'Larastan\Larastan\ReturnTypes\FormRequestSafeDynamicMethodReturnTypeExtension' => [['01062']],
		'Larastan\Larastan\ReturnTypes\EloquentCollectionMapDynamicReturnTypeExtension' => [['01063']],
		'Larastan\Larastan\Rules\NoAuthFacadeInRequestScopeRule' => [['01064']],
		'Larastan\Larastan\Rules\NoAuthHelperInRequestScopeRule' => [['01065']],
		'Larastan\Larastan\Rules\ConfigCollectionRule' => [['01066']],
		'Larastan\Larastan\Rules\Queue\UniqueJobDeclaresUniqueForRule' => [['01067']],
		'Larastan\Larastan\Rules\Queue\UniqueJobDeclaresUniqueIdRule' => [['01068']],
		'Larastan\Larastan\Rules\Queue\NoBatchedUniqueJobRule' => [['01069']],
		'Larastan\Larastan\Rules\Queue\JobWithModelPropertyDeclaresSerializesModelsRule' => [['01070']],
		'Larastan\Larastan\Rules\Queue\BatchedJobIsBatchableRule' => [['01071']],
		'Larastan\Larastan\Rules\Queue\BatchableJobChecksCancellationRule' => [['01072']],
		'Larastan\Larastan\Rules\Queue\JobDispatchedInTransactionUsesAfterCommitRule' => [['01073']],
		'Illuminate\Filesystem\Filesystem' => [['01074']],
	];


	public function __construct(array $params = [])
	{
		parent::__construct($params);
	}


	public function createService01(): PHPStan\Php\PhpVersionFactoryFactory
	{
		return new PHPStan\Php\PhpVersionFactoryFactory(
			$this->getParameter('phpVersion'),
			$this->getParameter('composerAutoloaderProjectPaths')
		);
	}


	public function createService02(): PHPStan\Php\ConfiguredPhpVersionRangeHelper
	{
		return new PHPStan\Php\ConfiguredPhpVersionRangeHelper($this->getParameter('phpVersion'), $this->getService('04'));
	}


	public function createService03(): PHPStan\Php\PhpVersion
	{
		return $this->getService('05')->create();
	}


	public function createService04(): PHPStan\Php\ComposerPhpVersionFactory
	{
		return new PHPStan\Php\ComposerPhpVersionFactory($this->getParameter('composerAutoloaderProjectPaths'));
	}


	public function createService05(): PHPStan\Php\PhpVersionFactory
	{
		return $this->getService('01')->create();
	}


	public function createService06(): PHPStan\File\FileMonitor
	{
		return new PHPStan\File\FileMonitor(
			$this->getService('fileFinderAnalyse'),
			$this->getService('fileFinderScan'),
			$this->getParameter('analysedPaths'),
			$this->getParameter('analysedPathsFromConfig'),
			$this->getParameter('scanFiles'),
			$this->getParameter('scanDirectories'),
			$this->getService('011')
		);
	}


	public function createService07(): PHPStan\File\FileContentHasher
	{
		return new PHPStan\File\FileContentHasher;
	}


	public function createService08(): PHPStan\File\FileStatSignatures
	{
		return new PHPStan\File\FileStatSignatures;
	}


	public function createService09(): PHPStan\File\FileHelper
	{
		return new PHPStan\File\FileHelper($this->getParameter('currentWorkingDirectory'));
	}


	public function createService010(): PHPStan\File\IncludedFilePathResolver
	{
		return new PHPStan\File\IncludedFilePathResolver($this->getParameter('currentWorkingDirectory'), $this->getService('09'));
	}


	public function createService011(): PHPStan\File\DirectoryWalker
	{
		return new PHPStan\File\DirectoryWalker($this->getService('08'), $this->getParameter('tmpDir'));
	}


	public function createService012(): PHPStan\File\DeduplicatingFileReader
	{
		return new PHPStan\File\DeduplicatingFileReader;
	}


	public function createService013(): PHPStan\File\FileExcluderFactory
	{
		return new PHPStan\File\FileExcluderFactory($this->getService('0563'), $this->getParameter('excludePaths'));
	}


	public function createService014(): PHPStan\PhpDoc\ReflectionEnumStubFilesExtension
	{
		return new PHPStan\PhpDoc\ReflectionEnumStubFilesExtension($this->getService('03'));
	}


	public function createService015(): PHPStan\PhpDoc\BcMathNumberStubFilesExtension
	{
		return new PHPStan\PhpDoc\BcMathNumberStubFilesExtension($this->getService('03'));
	}


	public function createService016(): PHPStan\PhpDoc\ReflectionClassStubFilesExtension
	{
		return new PHPStan\PhpDoc\ReflectionClassStubFilesExtension($this->getService('03'));
	}


	public function createService017(): PHPStan\PhpDoc\DefaultStubFilesProvider
	{
		return new PHPStan\PhpDoc\DefaultStubFilesProvider(
			$this->getService('phpstan.extensionsCollection.PHPStan.PhpDoc.StubFilesExtension'),
			$this->getService('09'),
			$this->getParameter('stubFiles'),
			$this->getParameter('composerAutoloaderProjectPaths')
		);
	}


	public function createService018(): PHPStan\PhpDoc\PhpDocInheritanceResolver
	{
		return new PHPStan\PhpDoc\PhpDocInheritanceResolver($this->getService('0556'));
	}


	public function createService019(): PHPStan\PhpDoc\PhpDocStringResolver
	{
		return new PHPStan\PhpDoc\PhpDocStringResolver($this->getService('0890'), $this->getService('0893'));
	}


	public function createService020(): PHPStan\PhpDoc\ConstExprNodeResolver
	{
		return new PHPStan\PhpDoc\ConstExprNodeResolver($this->getService('054'), $this->getService('072'));
	}


	public function createService021(): PHPStan\PhpDoc\PhpDocNodeResolver
	{
		return new PHPStan\PhpDoc\PhpDocNodeResolver($this->getService('026'), $this->getService('020'), $this->getService('0114'));
	}


	public function createService022(): PHPStan\PhpDoc\JsonValidateStubFilesExtension
	{
		return new PHPStan\PhpDoc\JsonValidateStubFilesExtension($this->getService('03'));
	}


	public function createService023(): PHPStan\PhpDoc\StubValidator
	{
		return new PHPStan\PhpDoc\StubValidator($this->getService('0332'), $this->getService('0333'), $this->getService('017'));
	}


	public function createService024(): PHPStan\PhpDoc\ExtDsStubFilesExtension
	{
		return new PHPStan\PhpDoc\ExtDsStubFilesExtension($this->getService('066'));
	}


	public function createService025(): PHPStan\PhpDoc\TypeStringResolver
	{
		return new PHPStan\PhpDoc\TypeStringResolver($this->getService('0890'), $this->getService('0891'), $this->getService('026'));
	}


	public function createService026(): PHPStan\PhpDoc\TypeNodeResolver
	{
		return new PHPStan\PhpDoc\TypeNodeResolver(
			$this->getService('027'),
			$this->getService('054'),
			$this->getService('0559'),
			$this->getService('0187'),
			$this->getService('072'),
			$this->getParameter('reportUnsafeArrayStringKeyCasting')
		);
	}


	public function createService027(): PHPStan\PhpDoc\LazyTypeNodeResolverExtensionRegistryProvider
	{
		return new PHPStan\PhpDoc\LazyTypeNodeResolverExtensionRegistryProvider($this->getService('0333'));
	}


	public function createService028(): PHPStan\PhpDoc\SocketSelectStubFilesExtension
	{
		return new PHPStan\PhpDoc\SocketSelectStubFilesExtension($this->getService('03'));
	}


	public function createService029(): PHPStan\Cache\Cache
	{
		return new PHPStan\Cache\Cache($this->getService('cacheStorage'));
	}


	public function createService030(): PHPStan\Broker\AnonymousClassNameHelper
	{
		return new PHPStan\Broker\AnonymousClassNameHelper($this->getService('09'), $this->getService('simpleRelativePathHelper'));
	}


	public function createService031(): PHPStan\Dependency\ExportedNodeVisitor
	{
		return new PHPStan\Dependency\ExportedNodeVisitor($this->getService('035'));
	}


	public function createService032(): PHPStan\Dependency\PackageDependencyResolver
	{
		return new PHPStan\Dependency\PackageDependencyResolver(
			$this->getParameter('composerAutoloaderProjectPaths'),
			$this->getService('09')
		);
	}


	public function createService033(): PHPStan\Dependency\DependencyResolver
	{
		return new PHPStan\Dependency\DependencyResolver(
			$this->getService('09'),
			$this->getService('reflectionProvider'),
			$this->getService('035'),
			$this->getService('032')
		);
	}


	public function createService034(): PHPStan\Dependency\ExportedNodeFetcher
	{
		return new PHPStan\Dependency\ExportedNodeFetcher($this->getService('defaultAnalysisParser'), $this->getService('031'));
	}


	public function createService035(): PHPStan\Dependency\ExportedNodeResolver
	{
		return new PHPStan\Dependency\ExportedNodeResolver($this->getService('reflectionProvider'), $this->getService('081'));
	}


	public function createService036(): PHPStan\Parallel\ParallelAnalyser
	{
		return new PHPStan\Parallel\ParallelAnalyser(
			$this->getParameter('internalErrorsCountLimit'),
			$this->getParameter('parallel')['processTimeout'],
			$this->getParameter('parallel')['buffer'],
			$this->getService('038'),
			$this->getService('058'),
			$this->getService('037')
		);
	}


	public function createService037(): PHPStan\Parallel\WorkerRunner
	{
		return new PHPStan\Parallel\WorkerRunner(
			$this->getService('0342'),
			$this->getService('0321'),
			$this->getService('registry'),
			$this->getService('0336'),
			$this->getService('0193'),
			$this->getParameter('parallel')['buffer']
		);
	}


	public function createService038(): PHPStan\Parallel\ForkParallelChecker
	{
		return new PHPStan\Parallel\ForkParallelChecker;
	}


	public function createService039(): PHPStan\Parallel\Scheduler
	{
		return new PHPStan\Parallel\Scheduler(
			$this->getParameter('parallel')['jobSize'],
			$this->getParameter('parallel')['maximumNumberOfProcesses'],
			$this->getParameter('parallel')['minimumNumberOfJobsPerProcess']
		);
	}


	public function createService040(): PHPStan\Process\Restart\RestartDiagnoseExtension
	{
		return new PHPStan\Process\Restart\RestartDiagnoseExtension;
	}


	public function createService041(): PHPStan\Process\CpuCoreCounter
	{
		return new PHPStan\Process\CpuCoreCounter($this->getParameter('parallel')['loadLimit']);
	}


	public function createService042(): PHPStan\Reflection\Mixin\MixinPropertiesClassReflectionExtension
	{
		return new PHPStan\Reflection\Mixin\MixinPropertiesClassReflectionExtension($this->getParameter('mixinExcludeClasses'));
	}


	public function createService043(): PHPStan\Reflection\Mixin\MixinMethodsClassReflectionExtension
	{
		return new PHPStan\Reflection\Mixin\MixinMethodsClassReflectionExtension($this->getParameter('mixinExcludeClasses'));
	}


	public function createService044(): PHPStan\Reflection\Php\EnumAllowedSubTypesClassReflectionExtension
	{
		return new PHPStan\Reflection\Php\EnumAllowedSubTypesClassReflectionExtension;
	}


	public function createService045(): PHPStan\Reflection\Php\PhpClassReflectionExtension
	{
		return new PHPStan\Reflection\Php\PhpClassReflectionExtension(
			$this->getService('0191'),
			$this->getService('0186'),
			$this->getService('0193'),
			$this->getService('0564'),
			$this->getService('018'),
			$this->getService('079'),
			$this->getService('050'),
			$this->getService('051'),
			$this->getService('075'),
			$this->getService('defaultAnalysisParser'),
			$this->getService('stubPhpDocProvider'),
			$this->getService('054'),
			$this->getService('0556'),
			$this->getService('052'),
			$this->getService('053'),
			$this->getParameter('inferPrivatePropertyTypeFromConstructor'),
			$this->getService('03'),
			$this->getParameter('featureToggles')['backedEnumFromParameterType'],
			$this->getParameter('cache')['memberCacheKeysMax']
		);
	}


	public function createService046(): PHPStan\Reflection\Php\SealedAllowedSubTypesClassReflectionExtension
	{
		return new PHPStan\Reflection\Php\SealedAllowedSubTypesClassReflectionExtension;
	}


	public function createService047(): PHPStan\Reflection\Php\UniversalObjectCratesClassReflectionExtension
	{
		return new PHPStan\Reflection\Php\UniversalObjectCratesClassReflectionExtension(
			$this->getService('reflectionProvider'),
			$this->getParameter('universalObjectCratesClasses'),
			$this->getService('051')
		);
	}


	public function createService048(): PHPStan\Reflection\Php\Soap\SoapClientMethodsClassReflectionExtension
	{
		return new PHPStan\Reflection\Php\Soap\SoapClientMethodsClassReflectionExtension;
	}


	public function createService049(): PHPStan\Reflection\ConstructorsHelper
	{
		return new PHPStan\Reflection\ConstructorsHelper(
			$this->getService('phpstan.extensionsCollection.PHPStan.Reflection.AdditionalConstructorsExtension'),
			$this->getParameter('additionalConstructors')
		);
	}


	public function createService050(): PHPStan\Reflection\Annotations\AnnotationsMethodsClassReflectionExtension
	{
		return new PHPStan\Reflection\Annotations\AnnotationsMethodsClassReflectionExtension;
	}


	public function createService051(): PHPStan\Reflection\Annotations\AnnotationsPropertiesClassReflectionExtension
	{
		return new PHPStan\Reflection\Annotations\AnnotationsPropertiesClassReflectionExtension;
	}


	public function createService052(): PHPStan\Reflection\AttributeReflectionFactory
	{
		return new PHPStan\Reflection\AttributeReflectionFactory($this->getService('072'), $this->getService('054'));
	}


	public function createService053(): PHPStan\Reflection\ParameterAllowedConstantsMapProvider
	{
		return new PHPStan\Reflection\ParameterAllowedConstantsMapProvider;
	}


	public function createService054(): PHPStan\Reflection\ReflectionProvider\LazyReflectionProviderProvider
	{
		return new PHPStan\Reflection\ReflectionProvider\LazyReflectionProviderProvider($this->getService('0333'));
	}


	public function createService055(): PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedDirectorySourceLocatorRepository
	{
		return new PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedDirectorySourceLocatorRepository($this->getService('060'));
	}


	public function createService056(): PHPStan\Reflection\BetterReflection\SourceLocator\FileNodesFetcher
	{
		return new PHPStan\Reflection\BetterReflection\SourceLocator\FileNodesFetcher(
			$this->getService('057'),
			$this->getService('defaultAnalysisParser'),
			$this->getService('012')
		);
	}


	public function createService057(): PHPStan\Reflection\BetterReflection\SourceLocator\CachingVisitor
	{
		return new PHPStan\Reflection\BetterReflection\SourceLocator\CachingVisitor;
	}


	public function createService058(): PHPStan\Reflection\BetterReflection\SourceLocator\PreForkDirectorySymbolScanner
	{
		return new PHPStan\Reflection\BetterReflection\SourceLocator\PreForkDirectorySymbolScanner(
			$this->getService('059'),
			$this->getService('055'),
			$this->getService('060'),
			$this->getParameter('composerAutoloaderProjectPaths'),
			$this->getParameter('analysedPaths'),
			$this->getParameter('analysedPathsFromConfig'),
			$this->getParameter('scanDirectories')
		);
	}


	public function createService059(): PHPStan\Reflection\BetterReflection\SourceLocator\ComposerJsonAndInstalledJsonSourceLocatorMaker
	{
		return new PHPStan\Reflection\BetterReflection\SourceLocator\ComposerJsonAndInstalledJsonSourceLocatorMaker(
			$this->getService('055'),
			$this->getService('0567'),
			$this->getService('060'),
			$this->getService('03')
		);
	}


	public function createService060(): PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedDirectorySourceLocatorFactory
	{
		return new PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedDirectorySourceLocatorFactory(
			$this->getService('056'),
			$this->getService('fileFinderScan'),
			$this->getService('03'),
			$this->getService('061'),
			$this->getService('029'),
			$this->getService('07'),
			$this->getService('08'),
			$this->getParameter('tmpDir')
		);
	}


	public function createService061(): PHPStan\Reflection\BetterReflection\SourceLocator\SymbolFinderInFiles
	{
		return new PHPStan\Reflection\BetterReflection\SourceLocator\SymbolFinderInFiles($this->getService('062'));
	}


	public function createService062(): PHPStan\Reflection\BetterReflection\SourceLocator\PhpFileCleaner
	{
		return new PHPStan\Reflection\BetterReflection\SourceLocator\PhpFileCleaner;
	}


	public function createService063(): PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedSingleFileSourceLocatorRepository
	{
		return new PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedSingleFileSourceLocatorRepository($this->getService('0566'));
	}


	public function createService064(): PHPStan\Reflection\BetterReflection\SourceLocator\BootstrapAutoloaderExceptionReporter
	{
		return new PHPStan\Reflection\BetterReflection\SourceLocator\BootstrapAutoloaderExceptionReporter;
	}


	public function createService065(): PHPStan\Reflection\BetterReflection\BetterReflectionSourceLocatorFactory
	{
		return new PHPStan\Reflection\BetterReflection\BetterReflectionSourceLocatorFactory(
			$this->getService('phpParserDecorator'),
			$this->getService('php8PhpParser'),
			$this->getService('029'),
			$this->getService('09'),
			$this->getService('03'),
			$this->getService('0895'),
			$this->getService('066'),
			$this->getService('0896'),
			$this->getService('063'),
			$this->getService('055'),
			$this->getService('060'),
			$this->getService('059'),
			$this->getService('0567'),
			$this->getService('056'),
			$this->getService('064'),
			$this->getParameter('scanFiles'),
			$this->getParameter('scanDirectories'),
			$this->getParameter('analysedPaths'),
			$this->getParameter('composerAutoloaderProjectPaths'),
			$this->getParameter('analysedPathsFromConfig'),
			$this->getParameter('sourceLocatorPlaygroundMode'),
			$this->getParameter('singleReflectionFile')
		);
	}


	public function createService066(): PHPStan\Reflection\BetterReflection\SourceStubber\ExtensionVersionProvider
	{
		return new PHPStan\Reflection\BetterReflection\SourceStubber\ExtensionVersionProvider($this->getParameter('composerAutoloaderProjectPaths'));
	}


	public function createService067(): PHPStan\Reflection\BetterReflection\SourceStubber\PhpStormStubsSourceStubberFactory
	{
		return new PHPStan\Reflection\BetterReflection\SourceStubber\PhpStormStubsSourceStubberFactory(
			$this->getService('php8PhpParser'),
			$this->getService('080'),
			$this->getService('03'),
			$this->getParameter('cache')['phpStormStubsNodesCountMax'],
			$this->getService('066')
		);
	}


	public function createService068(): PHPStan\Reflection\BetterReflection\SourceStubber\ReflectionSourceStubberFactory
	{
		return new PHPStan\Reflection\BetterReflection\SourceStubber\ReflectionSourceStubberFactory(
			$this->getService('080'),
			$this->getService('03')
		);
	}


	public function createService069(): PHPStan\Reflection\BetterReflection\Type\AdapterReflectionEnumDynamicReturnTypeExtension
	{
		return new PHPStan\Reflection\BetterReflection\Type\AdapterReflectionEnumDynamicReturnTypeExtension;
	}


	public function createService070(): PHPStan\Reflection\RequireExtension\RequireExtendsPropertiesClassReflectionExtension
	{
		return new PHPStan\Reflection\RequireExtension\RequireExtendsPropertiesClassReflectionExtension;
	}


	public function createService071(): PHPStan\Reflection\RequireExtension\RequireExtendsMethodsClassReflectionExtension
	{
		return new PHPStan\Reflection\RequireExtension\RequireExtendsMethodsClassReflectionExtension;
	}


	public function createService072(): PHPStan\Reflection\InitializerExprTypeResolver
	{
		return new PHPStan\Reflection\InitializerExprTypeResolver(
			$this->getService('0187'),
			$this->getService('054'),
			$this->getService('03'),
			$this->getService('0554'),
			$this->getService('0552'),
			$this->getService('0555'),
			$this->getParameter('usePathConstantsAsConstantString')
		);
	}


	public function createService073(): PHPStan\Reflection\SignatureMap\Php8SignatureMapProvider
	{
		return new PHPStan\Reflection\SignatureMap\Php8SignatureMapProvider(
			$this->getService('078'),
			$this->getService('056'),
			$this->getService('0556'),
			$this->getService('03'),
			$this->getService('072'),
			$this->getService('054')
		);
	}


	public function createService074(): PHPStan\Reflection\SignatureMap\SignatureMapProviderFactory
	{
		return new PHPStan\Reflection\SignatureMap\SignatureMapProviderFactory(
			$this->getService('03'),
			$this->getService('078'),
			$this->getService('073')
		);
	}


	public function createService075(): PHPStan\Reflection\SignatureMap\SignatureMapProvider
	{
		return $this->getService('074')->create();
	}


	public function createService076(): PHPStan\Reflection\SignatureMap\NativeFunctionReflectionProvider
	{
		return new PHPStan\Reflection\SignatureMap\NativeFunctionReflectionProvider(
			$this->getService('075'),
			$this->getService('betterReflectionReflector'),
			$this->getService('0556'),
			$this->getService('stubPhpDocProvider'),
			$this->getService('052'),
			$this->getService('053')
		);
	}


	public function createService077(): PHPStan\Reflection\SignatureMap\SignatureMapParser
	{
		return new PHPStan\Reflection\SignatureMap\SignatureMapParser($this->getService('025'));
	}


	public function createService078(): PHPStan\Reflection\SignatureMap\FunctionSignatureMapProvider
	{
		return new PHPStan\Reflection\SignatureMap\FunctionSignatureMapProvider(
			$this->getService('077'),
			$this->getService('072'),
			$this->getService('03'),
			$this->getParameter('featureToggles')['stricterFunctionMap']
		);
	}


	public function createService079(): PHPStan\Reflection\Deprecation\DeprecationProvider
	{
		return new PHPStan\Reflection\Deprecation\DeprecationProvider(
			$this->getService('phpstan.extensionsCollection.PHPStan.Reflection.Deprecation.ClassDeprecationExtension'),
			$this->getService('phpstan.extensionsCollection.PHPStan.Reflection.Deprecation.ClassConstantDeprecationExtension'),
			$this->getService('phpstan.extensionsCollection.PHPStan.Reflection.Deprecation.ConstantDeprecationExtension'),
			$this->getService('phpstan.extensionsCollection.PHPStan.Reflection.Deprecation.EnumCaseDeprecationExtension'),
			$this->getService('phpstan.extensionsCollection.PHPStan.Reflection.Deprecation.FunctionDeprecationExtension'),
			$this->getService('phpstan.extensionsCollection.PHPStan.Reflection.Deprecation.MethodDeprecationExtension'),
			$this->getService('phpstan.extensionsCollection.PHPStan.Reflection.Deprecation.PropertyDeprecationExtension')
		);
	}


	public function createService080(): PHPStan\Node\Printer\Printer
	{
		return new PHPStan\Node\Printer\Printer;
	}


	public function createService081(): PHPStan\Node\Printer\ExprPrinter
	{
		return new PHPStan\Node\Printer\ExprPrinter($this->getService('080'));
	}


	public function createService082(): PHPStan\Turbo\TurboDiagnoseExtension
	{
		return new PHPStan\Turbo\TurboDiagnoseExtension;
	}


	public function createService083(): PHPStan\Parser\StandaloneThrowExprVisitor
	{
		return new PHPStan\Parser\StandaloneThrowExprVisitor;
	}


	public function createService084(): PHPStan\Parser\ImplodeArgVisitor
	{
		return new PHPStan\Parser\ImplodeArgVisitor;
	}


	public function createService085(): PHPStan\Parser\ArrayFilterArgVisitor
	{
		return new PHPStan\Parser\ArrayFilterArgVisitor;
	}


	public function createService086(): PHPStan\Parser\ImmediatelyInvokedClosureVisitor
	{
		return new PHPStan\Parser\ImmediatelyInvokedClosureVisitor;
	}


	public function createService087(): PHPStan\Parser\CurlSetOptArrayArgVisitor
	{
		return new PHPStan\Parser\CurlSetOptArrayArgVisitor;
	}


	public function createService088(): PHPStan\Parser\TryCatchTypeVisitor
	{
		return new PHPStan\Parser\TryCatchTypeVisitor;
	}


	public function createService089(): PHPStan\Parser\ArrayWalkArgVisitor
	{
		return new PHPStan\Parser\ArrayWalkArgVisitor;
	}


	public function createService090(): PHPStan\Parser\LexerFactory
	{
		return new PHPStan\Parser\LexerFactory($this->getService('03'));
	}


	public function createService091(): PHPStan\Parser\ParentStmtTypesVisitor
	{
		return new PHPStan\Parser\ParentStmtTypesVisitor;
	}


	public function createService092(): PHPStan\Parser\NewAssignedToPropertyVisitor
	{
		return new PHPStan\Parser\NewAssignedToPropertyVisitor;
	}


	public function createService093(): PHPStan\Parser\DeclarePositionVisitor
	{
		return new PHPStan\Parser\DeclarePositionVisitor;
	}


	public function createService094(): PHPStan\Parser\ClosureBindToVarVisitor
	{
		return new PHPStan\Parser\ClosureBindToVarVisitor;
	}


	public function createService095(): PHPStan\Parser\ArrayFindArgVisitor
	{
		return new PHPStan\Parser\ArrayFindArgVisitor;
	}


	public function createService096(): PHPStan\Parser\ArrayMapArgVisitor
	{
		return new PHPStan\Parser\ArrayMapArgVisitor;
	}


	public function createService097(): PHPStan\Parser\ClosureArgVisitor
	{
		return new PHPStan\Parser\ClosureArgVisitor;
	}


	public function createService098(): PHPStan\Parser\ArrayOffsetNormalizingVisitor
	{
		return new PHPStan\Parser\ArrayOffsetNormalizingVisitor;
	}


	public function createService099(): PHPStan\Parser\GotoLabelVisitor
	{
		return new PHPStan\Parser\GotoLabelVisitor;
	}


	public function createService0100(): PHPStan\Parser\TypeTraverserInstanceofVisitor
	{
		return new PHPStan\Parser\TypeTraverserInstanceofVisitor;
	}


	public function createService0101(): PHPStan\Parser\ClosureBindArgVisitor
	{
		return new PHPStan\Parser\ClosureBindArgVisitor;
	}


	public function createService0102(): PHPStan\Parser\UseAliasVisitor
	{
		return new PHPStan\Parser\UseAliasVisitor;
	}


	public function createService0103(): PHPStan\Parser\LastConditionVisitor
	{
		return new PHPStan\Parser\LastConditionVisitor;
	}


	public function createService0104(): PHPStan\Parser\MagicConstantParamDefaultVisitor
	{
		return new PHPStan\Parser\MagicConstantParamDefaultVisitor;
	}


	public function createService0105(): PHPStan\Parser\ArrowFunctionArgVisitor
	{
		return new PHPStan\Parser\ArrowFunctionArgVisitor;
	}


	public function createService0106(): PHPStan\Parser\AnonymousClassVisitor
	{
		return new PHPStan\Parser\AnonymousClassVisitor;
	}


	public function createService0107(): PHPStan\Parser\CurlSetOptArgVisitor
	{
		return new PHPStan\Parser\CurlSetOptArgVisitor;
	}


	public function createService0108(): PHPStan\Diagnose\SystemResourcesDiagnoseExtension
	{
		return new PHPStan\Diagnose\SystemResourcesDiagnoseExtension(
			$this->getService('041'),
			$this->getParameter('parallel')['loadLimit']
		);
	}


	public function createService0109(): PHPStan\Rules\InternalTag\RestrictedInternalUsageHelper
	{
		return new PHPStan\Rules\InternalTag\RestrictedInternalUsageHelper;
	}


	public function createService0110(): PHPStan\Rules\ClassForbiddenNameCheck
	{
		return new PHPStan\Rules\ClassForbiddenNameCheck($this->getService('phpstan.extensionsCollection.PHPStan.Classes.ForbiddenClassNameExtension'));
	}


	public function createService0111(): PHPStan\Rules\PhpDoc\VarTagTypeRuleHelper
	{
		return new PHPStan\Rules\PhpDoc\VarTagTypeRuleHelper(
			$this->getService('026'),
			$this->getService('0556'),
			$this->getService('reflectionProvider'),
			$this->getParameter('reportWrongPhpDocTypeInVarTag'),
			$this->getParameter('reportAnyTypeWideningInVarTag'),
			$this->getService('072')
		);
	}


	public function createService0112(): PHPStan\Rules\PhpDoc\IncompatiblePhpDocTypeCheck
	{
		return new PHPStan\Rules\PhpDoc\IncompatiblePhpDocTypeCheck(
			$this->getService('0177'),
			$this->getService('0114'),
			$this->getService('0117')
		);
	}


	public function createService0113(): PHPStan\Rules\PhpDoc\RequireExtendsCheck
	{
		return new PHPStan\Rules\PhpDoc\RequireExtendsCheck(
			$this->getService('reflectionProvider'),
			$this->getService('0124'),
			$this->getParameter('checkClassCaseSensitivity'),
			$this->getParameter('tips')['discoveringSymbols']
		);
	}


	public function createService0114(): PHPStan\Rules\PhpDoc\UnresolvableTypeHelper
	{
		return new PHPStan\Rules\PhpDoc\UnresolvableTypeHelper;
	}


	public function createService0115(): PHPStan\Rules\PhpDoc\ConditionalReturnTypeRuleHelper
	{
		return new PHPStan\Rules\PhpDoc\ConditionalReturnTypeRuleHelper;
	}


	public function createService0116(): PHPStan\Rules\PhpDoc\AssertRuleHelper
	{
		return new PHPStan\Rules\PhpDoc\AssertRuleHelper(
			$this->getService('reflectionProvider'),
			$this->getService('0114'),
			$this->getService('0124'),
			$this->getService('0153'),
			$this->getService('0177'),
			$this->getParameter('checkClassCaseSensitivity'),
			$this->getParameter('checkMissingTypehints')
		);
	}


	public function createService0117(): PHPStan\Rules\PhpDoc\GenericCallableRuleHelper
	{
		return new PHPStan\Rules\PhpDoc\GenericCallableRuleHelper($this->getService('0178'));
	}


	public function createService0118(): PHPStan\Rules\RuleLevelHelper
	{
		return new PHPStan\Rules\RuleLevelHelper(
			$this->getService('reflectionProvider'),
			$this->getParameter('checkNullables'),
			$this->getParameter('checkThisOnly'),
			$this->getParameter('checkUnionTypes'),
			$this->getParameter('checkExplicitMixed'),
			$this->getParameter('checkImplicitMixed'),
			$this->getParameter('checkBenevolentUnionTypes'),
			$this->getParameter('tips')['discoveringSymbols']
		);
	}


	public function createService0119(): PHPStan\Rules\Exceptions\DefaultExceptionTypeResolver
	{
		return new PHPStan\Rules\Exceptions\DefaultExceptionTypeResolver(
			$this->getService('reflectionProvider'),
			$this->getParameter('exceptions')['uncheckedExceptionRegexes'],
			$this->getParameter('exceptions')['uncheckedExceptionClasses'],
			$this->getParameter('exceptions')['checkedExceptionRegexes'],
			$this->getParameter('exceptions')['checkedExceptionClasses']
		);
	}


	public function createService0120(): PHPStan\Rules\Exceptions\MissingCheckedExceptionInThrowsCheck
	{
		return new PHPStan\Rules\Exceptions\MissingCheckedExceptionInThrowsCheck($this->getService('exceptionTypeResolver'));
	}


	public function createService0121(): PHPStan\Rules\Exceptions\TooWideThrowTypeCheck
	{
		return new PHPStan\Rules\Exceptions\TooWideThrowTypeCheck($this->getParameter('exceptions')['implicitThrows']);
	}


	public function createService0122(): PHPStan\Rules\Arrays\NonexistentOffsetInArrayDimFetchCheck
	{
		return new PHPStan\Rules\Arrays\NonexistentOffsetInArrayDimFetchCheck(
			$this->getService('0118'),
			$this->getParameter('reportMaybes'),
			$this->getParameter('reportPossiblyNonexistentGeneralArrayOffset'),
			$this->getParameter('reportPossiblyNonexistentConstantArrayOffset')
		);
	}


	public function createService0123(): PHPStan\Rules\NonStringableDynamicAccessCheck
	{
		return new PHPStan\Rules\NonStringableDynamicAccessCheck(
			$this->getService('0118'),
			$this->getParameter('featureToggles')['checkNonStringableDynamicAccess']
		);
	}


	public function createService0124(): PHPStan\Rules\ClassNameCheck
	{
		return new PHPStan\Rules\ClassNameCheck(
			$this->getService('0150'),
			$this->getService('0110'),
			$this->getService('reflectionProvider'),
			$this->getService('phpstan.extensionsCollection.PHPStan.Rules.RestrictedUsage.RestrictedClassNameUsageExtension')
		);
	}


	public function createService0125(): PHPStan\Rules\Variables\ParameterOutTypeCheck
	{
		return new PHPStan\Rules\Variables\ParameterOutTypeCheck($this->getService('0118'));
	}


	public function createService0126(): PHPStan\Rules\NullsafeCheck
	{
		return new PHPStan\Rules\NullsafeCheck;
	}


	public function createService0127(): PHPStan\Rules\Comparison\ConstantConditionInTraitHelper
	{
		return new PHPStan\Rules\Comparison\ConstantConditionInTraitHelper($this->getService('081'), $this->getService('0189'));
	}


	public function createService0128(): PHPStan\Rules\Comparison\ImpossibleCheckTypeHelper
	{
		return new PHPStan\Rules\Comparison\ImpossibleCheckTypeHelper(
			$this->getService('reflectionProvider'),
			$this->getParameter('treatPhpDocTypesAsCertain')
		);
	}


	public function createService0129(): PHPStan\Rules\Comparison\ConstantConditionRuleHelper
	{
		return new PHPStan\Rules\Comparison\ConstantConditionRuleHelper($this->getParameter('treatPhpDocTypesAsCertain'));
	}


	public function createService0130(): PHPStan\Rules\Comparison\FunctionCallConstantConditionHelper
	{
		return new PHPStan\Rules\Comparison\FunctionCallConstantConditionHelper($this->getService('081'), $this->getService('0189'));
	}


	public function createService0131(): PHPStan\Rules\Properties\PropertyDescriptor
	{
		return new PHPStan\Rules\Properties\PropertyDescriptor;
	}


	public function createService0132(): PHPStan\Rules\Properties\AccessPropertiesCheck
	{
		return new PHPStan\Rules\Properties\AccessPropertiesCheck(
			$this->getService('reflectionProvider'),
			$this->getService('0118'),
			$this->getService('03'),
			$this->getService('0123'),
			$this->getParameter('reportMagicProperties'),
			$this->getParameter('checkDynamicProperties')
		);
	}


	public function createService0133(): PHPStan\Rules\Properties\PropertyReflectionFinder
	{
		return new PHPStan\Rules\Properties\PropertyReflectionFinder;
	}


	public function createService0134(): PHPStan\Rules\Properties\AccessStaticPropertiesCheck
	{
		return new PHPStan\Rules\Properties\AccessStaticPropertiesCheck(
			$this->getService('reflectionProvider'),
			$this->getService('0118'),
			$this->getService('0124'),
			$this->getService('03'),
			$this->getService('0123'),
			$this->getParameter('tips')['discoveringSymbols']
		);
	}


	public function createService0135(): PHPStan\Rules\Debug\FileAssertRule
	{
		return new PHPStan\Rules\Debug\FileAssertRule($this->getService('reflectionProvider'), $this->getService('025'));
	}


	public function createService0136(): PHPStan\Rules\Debug\DumpPhpDocTypeRule
	{
		return new PHPStan\Rules\Debug\DumpPhpDocTypeRule($this->getService('reflectionProvider'), $this->getService('0894'));
	}


	public function createService0137(): PHPStan\Rules\Debug\DumpTypeRule
	{
		return new PHPStan\Rules\Debug\DumpTypeRule($this->getService('reflectionProvider'));
	}


	public function createService0138(): PHPStan\Rules\Debug\DumpNativeTypeRule
	{
		return new PHPStan\Rules\Debug\DumpNativeTypeRule($this->getService('reflectionProvider'));
	}


	public function createService0139(): PHPStan\Rules\Debug\DebugScopeRule
	{
		return new PHPStan\Rules\Debug\DebugScopeRule($this->getService('reflectionProvider'));
	}


	public function createService0140(): PHPStan\Rules\Classes\LocalTypeAliasesCheck
	{
		return new PHPStan\Rules\Classes\LocalTypeAliasesCheck(
			$this->getParameter('typeAliases'),
			$this->getService('reflectionProvider'),
			$this->getService('026'),
			$this->getService('0153'),
			$this->getService('0124'),
			$this->getService('0114'),
			$this->getService('0177'),
			$this->getParameter('checkMissingTypehints'),
			$this->getParameter('checkClassCaseSensitivity'),
			$this->getParameter('tips')['discoveringSymbols']
		);
	}


	public function createService0141(): PHPStan\Rules\Classes\PropertyTagCheck
	{
		return new PHPStan\Rules\Classes\PropertyTagCheck(
			$this->getService('reflectionProvider'),
			$this->getService('0124'),
			$this->getService('0177'),
			$this->getService('0153'),
			$this->getService('0114'),
			$this->getParameter('checkClassCaseSensitivity'),
			$this->getParameter('checkMissingTypehints'),
			$this->getParameter('tips')['discoveringSymbols']
		);
	}


	public function createService0142(): PHPStan\Rules\Classes\MixinCheck
	{
		return new PHPStan\Rules\Classes\MixinCheck(
			$this->getService('reflectionProvider'),
			$this->getService('0124'),
			$this->getService('0177'),
			$this->getService('0153'),
			$this->getService('0114'),
			$this->getParameter('checkClassCaseSensitivity'),
			$this->getParameter('checkMissingTypehints'),
			$this->getParameter('tips')['discoveringSymbols']
		);
	}


	public function createService0143(): PHPStan\Rules\Classes\DuplicateDeclarationHelper
	{
		return new PHPStan\Rules\Classes\DuplicateDeclarationHelper;
	}


	public function createService0144(): PHPStan\Rules\Classes\MethodTagCheck
	{
		return new PHPStan\Rules\Classes\MethodTagCheck(
			$this->getService('reflectionProvider'),
			$this->getService('0124'),
			$this->getService('0177'),
			$this->getService('0153'),
			$this->getService('0114'),
			$this->getParameter('checkClassCaseSensitivity'),
			$this->getParameter('checkMissingTypehints'),
			$this->getParameter('tips')['discoveringSymbols']
		);
	}


	public function createService0145(): PHPStan\Rules\Classes\ConsistentConstructorHelper
	{
		return new PHPStan\Rules\Classes\ConsistentConstructorHelper;
	}


	public function createService0146(): PHPStan\Rules\FunctionCallParametersCheck
	{
		return new PHPStan\Rules\FunctionCallParametersCheck(
			$this->getService('0118'),
			$this->getService('0126'),
			$this->getService('0114'),
			$this->getService('0133'),
			$this->getService('reflectionProvider'),
			$this->getParameter('checkFunctionArgumentTypes'),
			$this->getParameter('checkArgumentsPassedByReference'),
			$this->getParameter('checkExtraArguments'),
			$this->getParameter('checkMissingTypehints')
		);
	}


	public function createService0147(): PHPStan\Rules\IssetCheck
	{
		return new PHPStan\Rules\IssetCheck(
			$this->getService('0131'),
			$this->getParameter('checkAdvancedIsset'),
			$this->getParameter('treatPhpDocTypesAsCertain')
		);
	}


	public function createService0148(): PHPStan\Rules\ParameterCastableToStringCheck
	{
		return new PHPStan\Rules\ParameterCastableToStringCheck($this->getService('0118'));
	}


	public function createService0149(): PHPStan\Rules\Api\ApiRuleHelper
	{
		return new PHPStan\Rules\Api\ApiRuleHelper;
	}


	public function createService0150(): PHPStan\Rules\ClassCaseSensitivityCheck
	{
		return new PHPStan\Rules\ClassCaseSensitivityCheck(
			$this->getService('reflectionProvider'),
			$this->getParameter('checkInternalClassCaseSensitivity'),
			$this->getParameter('featureToggles')['checkImportedClassNameCase']
		);
	}


	public function createService0151(): PHPStan\Rules\DeadCode\PossiblyPureCallTransitivePurityResolver
	{
		return new PHPStan\Rules\DeadCode\PossiblyPureCallTransitivePurityResolver($this->getService('reflectionProvider'));
	}


	public function createService0152(): PHPStan\Rules\UnusedParametersCheck
	{
		return new PHPStan\Rules\UnusedParametersCheck;
	}


	public function createService0153(): PHPStan\Rules\MissingTypehintCheck
	{
		return new PHPStan\Rules\MissingTypehintCheck(
			$this->getParameter('checkMissingCallableSignature'),
			$this->getParameter('featureToggles')['skipCheckGenericClasses'],
			$this->getParameter('featureToggles')['checkGenericIterableClasses']
		);
	}


	public function createService0154(): PHPStan\Rules\TooWideTypehints\TooWideTypeCheck
	{
		return new PHPStan\Rules\TooWideTypehints\TooWideTypeCheck(
			$this->getService('0133'),
			$this->getParameter('featureToggles')['reportTooWideBool'],
			$this->getParameter('featureToggles')['reportNestedTooWideType']
		);
	}


	public function createService0155(): PHPStan\Rules\TooWideTypehints\TooWideParameterOutTypeCheck
	{
		return new PHPStan\Rules\TooWideTypehints\TooWideParameterOutTypeCheck($this->getService('0154'));
	}


	public function createService0156(): PHPStan\Rules\Pure\FunctionPurityCheck
	{
		return new PHPStan\Rules\Pure\FunctionPurityCheck;
	}


	public function createService0157(): PHPStan\Rules\RestrictedUsage\RestrictedMethodUsageRule
	{
		return new PHPStan\Rules\RestrictedUsage\RestrictedMethodUsageRule(
			$this->getService('phpstan.extensionsCollection.PHPStan.Rules.RestrictedUsage.RestrictedMethodUsageExtension'),
			$this->getService('reflectionProvider')
		);
	}


	public function createService0158(): PHPStan\Rules\RestrictedUsage\RestrictedFunctionCallableUsageRule
	{
		return new PHPStan\Rules\RestrictedUsage\RestrictedFunctionCallableUsageRule(
			$this->getService('phpstan.extensionsCollection.PHPStan.Rules.RestrictedUsage.RestrictedFunctionUsageExtension'),
			$this->getService('reflectionProvider')
		);
	}


	public function createService0159(): PHPStan\Rules\RestrictedUsage\RestrictedClassConstantUsageRule
	{
		return new PHPStan\Rules\RestrictedUsage\RestrictedClassConstantUsageRule(
			$this->getService('phpstan.extensionsCollection.PHPStan.Rules.RestrictedUsage.RestrictedClassConstantUsageExtension'),
			$this->getService('reflectionProvider'),
			$this->getService('0118')
		);
	}


	public function createService0160(): PHPStan\Rules\RestrictedUsage\RestrictedStaticMethodUsageRule
	{
		return new PHPStan\Rules\RestrictedUsage\RestrictedStaticMethodUsageRule(
			$this->getService('phpstan.extensionsCollection.PHPStan.Rules.RestrictedUsage.RestrictedMethodUsageExtension'),
			$this->getService('reflectionProvider'),
			$this->getService('0118')
		);
	}


	public function createService0161(): PHPStan\Rules\RestrictedUsage\RestrictedFunctionUsageRule
	{
		return new PHPStan\Rules\RestrictedUsage\RestrictedFunctionUsageRule(
			$this->getService('phpstan.extensionsCollection.PHPStan.Rules.RestrictedUsage.RestrictedFunctionUsageExtension'),
			$this->getService('reflectionProvider')
		);
	}


	public function createService0162(): PHPStan\Rules\RestrictedUsage\RestrictedStaticMethodCallableUsageRule
	{
		return new PHPStan\Rules\RestrictedUsage\RestrictedStaticMethodCallableUsageRule(
			$this->getService('phpstan.extensionsCollection.PHPStan.Rules.RestrictedUsage.RestrictedMethodUsageExtension'),
			$this->getService('reflectionProvider'),
			$this->getService('0118')
		);
	}


	public function createService0163(): PHPStan\Rules\RestrictedUsage\RestrictedPropertyUsageRule
	{
		return new PHPStan\Rules\RestrictedUsage\RestrictedPropertyUsageRule(
			$this->getService('phpstan.extensionsCollection.PHPStan.Rules.RestrictedUsage.RestrictedPropertyUsageExtension'),
			$this->getService('reflectionProvider')
		);
	}


	public function createService0164(): PHPStan\Rules\RestrictedUsage\RestrictedUsageOfDeprecatedStringCastRule
	{
		return new PHPStan\Rules\RestrictedUsage\RestrictedUsageOfDeprecatedStringCastRule(
			$this->getService('phpstan.extensionsCollection.PHPStan.Rules.RestrictedUsage.RestrictedMethodUsageExtension'),
			$this->getService('reflectionProvider')
		);
	}


	public function createService0165(): PHPStan\Rules\RestrictedUsage\RestrictedMethodCallableUsageRule
	{
		return new PHPStan\Rules\RestrictedUsage\RestrictedMethodCallableUsageRule(
			$this->getService('phpstan.extensionsCollection.PHPStan.Rules.RestrictedUsage.RestrictedMethodUsageExtension'),
			$this->getService('reflectionProvider')
		);
	}


	public function createService0166(): PHPStan\Rules\RestrictedUsage\RestrictedStaticPropertyUsageRule
	{
		return new PHPStan\Rules\RestrictedUsage\RestrictedStaticPropertyUsageRule(
			$this->getService('phpstan.extensionsCollection.PHPStan.Rules.RestrictedUsage.RestrictedPropertyUsageExtension'),
			$this->getService('reflectionProvider'),
			$this->getService('0118')
		);
	}


	public function createService0167(): PHPStan\Rules\AttributesCheck
	{
		return new PHPStan\Rules\AttributesCheck(
			$this->getService('reflectionProvider'),
			$this->getService('0146'),
			$this->getService('0124'),
			$this->getParameter('deprecationRulesInstalled')
		);
	}


	public function createService0168(): PHPStan\Rules\Methods\ParentMethodHelper
	{
		return new PHPStan\Rules\Methods\ParentMethodHelper($this->getService('045'));
	}


	public function createService0169(): PHPStan\Rules\Methods\MethodCallCheck
	{
		return new PHPStan\Rules\Methods\MethodCallCheck(
			$this->getService('reflectionProvider'),
			$this->getService('0118'),
			$this->getParameter('checkFunctionNameCase'),
			$this->getParameter('reportMagicMethods')
		);
	}


	public function createService0170(): PHPStan\Rules\Methods\MethodVisibilityComparisonHelper
	{
		return new PHPStan\Rules\Methods\MethodVisibilityComparisonHelper;
	}


	public function createService0171(): PHPStan\Rules\Methods\StaticMethodCallCheck
	{
		return new PHPStan\Rules\Methods\StaticMethodCallCheck(
			$this->getService('reflectionProvider'),
			$this->getService('0118'),
			$this->getService('0124'),
			$this->getParameter('checkFunctionNameCase'),
			$this->getParameter('tips')['discoveringSymbols'],
			$this->getParameter('reportMagicMethods')
		);
	}


	public function createService0172(): PHPStan\Rules\Methods\MethodPrototypeFinder
	{
		return new PHPStan\Rules\Methods\MethodPrototypeFinder($this->getService('03'), $this->getService('045'));
	}


	public function createService0173(): PHPStan\Rules\Methods\MethodSignatureRule
	{
		return new PHPStan\Rules\Methods\MethodSignatureRule(
			$this->getService('0168'),
			$this->getParameter('reportMaybesInMethodSignatures'),
			$this->getParameter('reportStaticMethodSignatures'),
			$this->getParameter('featureToggles')['reportMethodPurityOverride']
		);
	}


	public function createService0174(): PHPStan\Rules\Methods\MethodParameterComparisonHelper
	{
		return new PHPStan\Rules\Methods\MethodParameterComparisonHelper($this->getService('03'));
	}


	public function createService0175(): PHPStan\Rules\FunctionReturnTypeCheck
	{
		return new PHPStan\Rules\FunctionReturnTypeCheck($this->getService('0118'));
	}


	public function createService0176(): PHPStan\Rules\Generics\GenericAncestorsCheck
	{
		return new PHPStan\Rules\Generics\GenericAncestorsCheck(
			$this->getService('reflectionProvider'),
			$this->getService('0177'),
			$this->getService('0181'),
			$this->getService('0114'),
			$this->getParameter('featureToggles')['skipCheckGenericClasses'],
			$this->getParameter('checkMissingTypehints')
		);
	}


	public function createService0177(): PHPStan\Rules\Generics\GenericObjectTypeCheck
	{
		return new PHPStan\Rules\Generics\GenericObjectTypeCheck;
	}


	public function createService0178(): PHPStan\Rules\Generics\TemplateTypeCheck
	{
		return new PHPStan\Rules\Generics\TemplateTypeCheck(
			$this->getService('reflectionProvider'),
			$this->getService('0124'),
			$this->getService('0177'),
			$this->getService('0562'),
			$this->getParameter('checkClassCaseSensitivity')
		);
	}


	public function createService0179(): PHPStan\Rules\Generics\MethodTagTemplateTypeCheck
	{
		return new PHPStan\Rules\Generics\MethodTagTemplateTypeCheck($this->getService('0556'), $this->getService('0178'));
	}


	public function createService0180(): PHPStan\Rules\Generics\CrossCheckInterfacesHelper
	{
		return new PHPStan\Rules\Generics\CrossCheckInterfacesHelper;
	}


	public function createService0181(): PHPStan\Rules\Generics\VarianceCheck
	{
		return new PHPStan\Rules\Generics\VarianceCheck;
	}


	public function createService0182(): PHPStan\Rules\Playground\NeverRuleHelper
	{
		return new PHPStan\Rules\Playground\NeverRuleHelper;
	}


	public function createService0183(): PHPStan\Rules\FunctionDefinitionCheck
	{
		return new PHPStan\Rules\FunctionDefinitionCheck(
			$this->getService('reflectionProvider'),
			$this->getService('0124'),
			$this->getService('0114'),
			$this->getService('03'),
			$this->getParameter('checkClassCaseSensitivity'),
			$this->getParameter('checkThisOnly'),
			$this->getParameter('featureToggles')['checkImportedClassNameCase']
		);
	}


	public function createService0184(): PHPStan\Internal\HttpClientFactory
	{
		return new PHPStan\Internal\HttpClientFactory;
	}


	public function createService0185(): PHPStan\Analyser\PropertyHookThrowPointsResolver
	{
		return new PHPStan\Analyser\PropertyHookThrowPointsResolver($this->getParameter('exceptions')['implicitThrows']);
	}


	public function createService0186(): PHPStan\Analyser\PhpDocsResolver
	{
		return new PHPStan\Analyser\PhpDocsResolver($this->getService('0556'), $this->getService('018'));
	}


	public function createService0187(): PHPStan\Analyser\ConstantResolver
	{
		return $this->getService('0195')->create();
	}


	public function createService0188(): PHPStan\Analyser\Analyser
	{
		return new PHPStan\Analyser\Analyser(
			$this->getService('0321'),
			$this->getService('registry'),
			$this->getService('0336'),
			$this->getService('0193'),
			$this->getParameter('internalErrorsCountLimit')
		);
	}


	public function createService0189(): PHPStan\Analyser\RuleErrorTransformer
	{
		return new PHPStan\Analyser\RuleErrorTransformer($this->getService('currentPhpVersionPhpParser'));
	}


	public function createService0190(): PHPStan\Analyser\ClosureProcessor
	{
		return new PHPStan\Analyser\ClosureProcessor(
			$this->getService('0333'),
			$this->getService('0569'),
			$this->getService('0274'),
			$this->getService('0265'),
			$this->getService('0266'),
			$this->getService('0330')
		);
	}


	public function createService0191(): PHPStan\Analyser\ScopeFactory
	{
		return new PHPStan\Analyser\ScopeFactory($this->getService('0570'));
	}


	public function createService0192(): PHPStan\Analyser\PropertyHooksProcessor
	{
		return new PHPStan\Analyser\PropertyHooksProcessor(
			$this->getService('0318'),
			$this->getService('0186'),
			$this->getService('0194'),
			$this->getService('0319')
		);
	}


	public function createService0193(): PHPStan\Analyser\NodeScopeResolver
	{
		return new PHPStan\Analyser\NodeScopeResolver(
			$this->getService('0333'),
			$this->getService('0328'),
			$this->getService('09'),
			$this->getService('phpstan.extensionsCollection.PHPStan.Analyser.PerFileAnalysisResettable'),
			$this->getService('0569'),
			$this->getService('0280')
		);
	}


	public function createService0194(): PHPStan\Analyser\AttributesHandler
	{
		return new PHPStan\Analyser\AttributesHandler($this->getService('reflectionProvider'), $this->getService('0281'));
	}


	public function createService0195(): PHPStan\Analyser\ConstantResolverFactory
	{
		return new PHPStan\Analyser\ConstantResolverFactory($this->getService('054'), $this->getService('0333'));
	}


	public function createService0196(): PHPStan\Analyser\ExprHandler\StaticPropertyFetchHandler
	{
		return new PHPStan\Analyser\ExprHandler\StaticPropertyFetchHandler(
			$this->getService('0133'),
			$this->getService('0569'),
			$this->getService('0270'),
			$this->getService('reflectionProvider')
		);
	}


	public function createService0197(): PHPStan\Analyser\ExprHandler\CoalesceHandler
	{
		return new PHPStan\Analyser\ExprHandler\CoalesceHandler(
			$this->getService('0264'),
			$this->getService('0569'),
			$this->getService('0270'),
			$this->getService('0261')
		);
	}


	public function createService0198(): PHPStan\Analyser\ExprHandler\EmptyHandler
	{
		return new PHPStan\Analyser\ExprHandler\EmptyHandler(
			$this->getService('0264'),
			$this->getService('0569'),
			$this->getService('0270'),
			$this->getService('0272')
		);
	}


	public function createService0199(): PHPStan\Analyser\ExprHandler\PreDecHandler
	{
		return new PHPStan\Analyser\ExprHandler\PreDecHandler(
			$this->getService('0569'),
			$this->getService('0268'),
			$this->getService('0270'),
			$this->getService('0208')
		);
	}


	public function createService0200(): PHPStan\Analyser\ExprHandler\ExitHandler
	{
		return new PHPStan\Analyser\ExprHandler\ExitHandler($this->getService('0569'), $this->getService('0270'));
	}


	public function createService0201(): PHPStan\Analyser\ExprHandler\UnaryPlusHandler
	{
		return new PHPStan\Analyser\ExprHandler\UnaryPlusHandler(
			$this->getService('072'),
			$this->getService('0569'),
			$this->getService('0270')
		);
	}


	public function createService0202(): PHPStan\Analyser\ExprHandler\ArrayHandler
	{
		return new PHPStan\Analyser\ExprHandler\ArrayHandler(
			$this->getService('072'),
			$this->getService('0569'),
			$this->getService('0265')
		);
	}


	public function createService0203(): PHPStan\Analyser\ExprHandler\ThrowHandler
	{
		return new PHPStan\Analyser\ExprHandler\ThrowHandler($this->getService('0569'), $this->getService('0270'));
	}


	public function createService0204(): PHPStan\Analyser\ExprHandler\IssetHandler
	{
		return new PHPStan\Analyser\ExprHandler\IssetHandler(
			$this->getService('0264'),
			$this->getService('0569'),
			$this->getService('0269'),
			$this->getService('0270'),
			$this->getService('0272')
		);
	}


	public function createService0205(): PHPStan\Analyser\ExprHandler\ShellExecHandler
	{
		return new PHPStan\Analyser\ExprHandler\ShellExecHandler(
			$this->getService('0271'),
			$this->getService('0569'),
			$this->getService('0270')
		);
	}


	public function createService0206(): PHPStan\Analyser\ExprHandler\ClosureHandler
	{
		return new PHPStan\Analyser\ExprHandler\ClosureHandler(
			$this->getService('0265'),
			$this->getService('0569'),
			$this->getService('0270'),
			$this->getService('0190'),
			$this->getService('0330')
		);
	}


	public function createService0207(): PHPStan\Analyser\ExprHandler\PostDecHandler
	{
		return new PHPStan\Analyser\ExprHandler\PostDecHandler(
			$this->getService('0569'),
			$this->getService('0270'),
			$this->getService('0268'),
			$this->getService('0208')
		);
	}


	public function createService0208(): PHPStan\Analyser\ExprHandler\AssignHandler
	{
		return new PHPStan\Analyser\ExprHandler\AssignHandler(
			$this->getService('0328'),
			$this->getService('0315'),
			$this->getService('03'),
			$this->getService('081'),
			$this->getService('0213'),
			$this->getService('0245'),
			$this->getService('0569'),
			$this->getService('0270'),
			$this->getService('0275'),
			$this->getService('0133'),
			$this->getService('0276'),
			$this->getService('0264'),
			$this->getService('0248'),
			$this->getService('0236'),
			$this->getService('0241'),
			$this->getService('0196'),
			$this->getService('0269'),
			$this->getService('0185'),
			$this->getService('0280'),
			$this->getService('0558')
		);
	}


	public function createService0209(): PHPStan\Analyser\ExprHandler\YieldFromHandler
	{
		return new PHPStan\Analyser\ExprHandler\YieldFromHandler($this->getService('0569'), $this->getService('0270'));
	}


	public function createService0210(): PHPStan\Analyser\ExprHandler\CastHandler
	{
		return new PHPStan\Analyser\ExprHandler\CastHandler(
			$this->getService('072'),
			$this->getService('0569'),
			$this->getService('0270'),
			$this->getService('0275')
		);
	}


	public function createService0211(): PHPStan\Analyser\ExprHandler\BinaryOpHandler
	{
		return new PHPStan\Analyser\ExprHandler\BinaryOpHandler(
			$this->getService('072'),
			$this->getService('0326'),
			$this->getService('03'),
			$this->getService('0271'),
			$this->getService('081'),
			$this->getService('0275'),
			$this->getService('0259'),
			$this->getService('0569'),
			$this->getService('0270')
		);
	}


	public function createService0212(): PHPStan\Analyser\ExprHandler\ArrowFunctionHandler
	{
		return new PHPStan\Analyser\ExprHandler\ArrowFunctionHandler(
			$this->getService('0265'),
			$this->getService('0569'),
			$this->getService('0270'),
			$this->getService('0190'),
			$this->getService('0330')
		);
	}


	public function createService0213(): PHPStan\Analyser\ExprHandler\MatchHandler
	{
		return new PHPStan\Analyser\ExprHandler\MatchHandler(
			$this->getParameter('treatPhpDocTypesAsCertain'),
			$this->getService('0569'),
			$this->getService('0270'),
			$this->getService('0275'),
			$this->getService('0326')
		);
	}


	public function createService0214(): PHPStan\Analyser\ExprHandler\Virtual\MethodCallableNodeHandler
	{
		return new PHPStan\Analyser\ExprHandler\Virtual\MethodCallableNodeHandler(
			$this->getService('0569'),
			$this->getService('0270'),
			$this->getService('072')
		);
	}


	public function createService0215(): PHPStan\Analyser\ExprHandler\Virtual\InstantiationCallableNodeHandler
	{
		return new PHPStan\Analyser\ExprHandler\Virtual\InstantiationCallableNodeHandler(
			$this->getService('0569'),
			$this->getService('0270'),
			$this->getService('072')
		);
	}


	public function createService0216(): PHPStan\Analyser\ExprHandler\Virtual\TypeExprHandler
	{
		return new PHPStan\Analyser\ExprHandler\Virtual\TypeExprHandler($this->getService('0276'));
	}


	public function createService0217(): PHPStan\Analyser\ExprHandler\Virtual\FunctionCallableNodeHandler
	{
		return new PHPStan\Analyser\ExprHandler\Virtual\FunctionCallableNodeHandler(
			$this->getService('0569'),
			$this->getService('0270'),
			$this->getService('072'),
			$this->getService('reflectionProvider')
		);
	}


	public function createService0218(): PHPStan\Analyser\ExprHandler\Virtual\NativeTypeExprHandler
	{
		return new PHPStan\Analyser\ExprHandler\Virtual\NativeTypeExprHandler($this->getService('0276'));
	}


	public function createService0219(): PHPStan\Analyser\ExprHandler\Virtual\AlwaysRememberedExprHandler
	{
		return new PHPStan\Analyser\ExprHandler\Virtual\AlwaysRememberedExprHandler(
			$this->getService('0569'),
			$this->getService('0270')
		);
	}


	public function createService0220(): PHPStan\Analyser\ExprHandler\Virtual\IssetExprHandler
	{
		return new PHPStan\Analyser\ExprHandler\Virtual\IssetExprHandler($this->getService('0569'), $this->getService('0270'));
	}


	public function createService0221(): PHPStan\Analyser\ExprHandler\Virtual\SetExistingOffsetValueTypeExprHandler
	{
		return new PHPStan\Analyser\ExprHandler\Virtual\SetExistingOffsetValueTypeExprHandler($this->getService('0569'));
	}


	public function createService0222(): PHPStan\Analyser\ExprHandler\Virtual\ExistingArrayDimFetchHandler
	{
		return new PHPStan\Analyser\ExprHandler\Virtual\ExistingArrayDimFetchHandler($this->getService('0569'));
	}


	public function createService0223(): PHPStan\Analyser\ExprHandler\Virtual\UnsetOffsetExprHandler
	{
		return new PHPStan\Analyser\ExprHandler\Virtual\UnsetOffsetExprHandler($this->getService('0276'));
	}


	public function createService0224(): PHPStan\Analyser\ExprHandler\Virtual\StaticMethodCallableNodeHandler
	{
		return new PHPStan\Analyser\ExprHandler\Virtual\StaticMethodCallableNodeHandler(
			$this->getService('0569'),
			$this->getService('0270'),
			$this->getService('072'),
			$this->getService('reflectionProvider')
		);
	}


	public function createService0225(): PHPStan\Analyser\ExprHandler\Virtual\SetOffsetValueTypeExprHandler
	{
		return new PHPStan\Analyser\ExprHandler\Virtual\SetOffsetValueTypeExprHandler($this->getService('0569'));
	}


	public function createService0226(): PHPStan\Analyser\ExprHandler\UnaryMinusHandler
	{
		return new PHPStan\Analyser\ExprHandler\UnaryMinusHandler(
			$this->getService('072'),
			$this->getService('0569'),
			$this->getService('0270')
		);
	}


	public function createService0227(): PHPStan\Analyser\ExprHandler\PipeHandler
	{
		return new PHPStan\Analyser\ExprHandler\PipeHandler($this->getService('0569'), $this->getService('0270'));
	}


	public function createService0228(): PHPStan\Analyser\ExprHandler\InterpolatedStringHandler
	{
		return new PHPStan\Analyser\ExprHandler\InterpolatedStringHandler(
			$this->getService('072'),
			$this->getService('0271'),
			$this->getService('0569'),
			$this->getService('0270')
		);
	}


	public function createService0229(): PHPStan\Analyser\ExprHandler\PreIncHandler
	{
		return new PHPStan\Analyser\ExprHandler\PreIncHandler(
			$this->getService('0569'),
			$this->getService('0268'),
			$this->getService('0270'),
			$this->getService('0208')
		);
	}


	public function createService0230(): PHPStan\Analyser\ExprHandler\BooleanAndHandler
	{
		return new PHPStan\Analyser\ExprHandler\BooleanAndHandler($this->getService('0272'), $this->getService('0569'));
	}


	public function createService0231(): PHPStan\Analyser\ExprHandler\YieldHandler
	{
		return new PHPStan\Analyser\ExprHandler\YieldHandler($this->getService('0569'), $this->getService('0270'));
	}


	public function createService0232(): PHPStan\Analyser\ExprHandler\PrintHandler
	{
		return new PHPStan\Analyser\ExprHandler\PrintHandler(
			$this->getService('0271'),
			$this->getService('0569'),
			$this->getService('0270')
		);
	}


	public function createService0233(): PHPStan\Analyser\ExprHandler\ScalarHandler
	{
		return new PHPStan\Analyser\ExprHandler\ScalarHandler($this->getService('072'), $this->getService('0569'));
	}


	public function createService0234(): PHPStan\Analyser\ExprHandler\StaticCallHandler
	{
		return new PHPStan\Analyser\ExprHandler\StaticCallHandler(
			$this->getService('0273'),
			$this->getService('0269'),
			$this->getService('reflectionProvider'),
			$this->getParameter('rememberPossiblyImpureFunctionValues'),
			$this->getService('0569'),
			$this->getService('typeSpecifier'),
			$this->getService('0270'),
			$this->getService('0263'),
			$this->getService('0258'),
			$this->getService('0281')
		);
	}


	public function createService0235(): PHPStan\Analyser\ExprHandler\NullsafePropertyFetchHandler
	{
		return new PHPStan\Analyser\ExprHandler\NullsafePropertyFetchHandler(
			$this->getService('0264'),
			$this->getService('0569'),
			$this->getService('0270'),
			$this->getService('0272')
		);
	}


	public function createService0236(): PHPStan\Analyser\ExprHandler\ArrayDimFetchHandler
	{
		return new PHPStan\Analyser\ExprHandler\ArrayDimFetchHandler(
			$this->getService('0569'),
			$this->getService('0270'),
			$this->getService('0269'),
			$this->getService('0273')
		);
	}


	public function createService0237(): PHPStan\Analyser\ExprHandler\ClassConstFetchHandler
	{
		return new PHPStan\Analyser\ExprHandler\ClassConstFetchHandler(
			$this->getService('072'),
			$this->getService('0569'),
			$this->getService('0270'),
			$this->getService('reflectionProvider')
		);
	}


	public function createService0238(): PHPStan\Analyser\ExprHandler\BitwiseNotHandler
	{
		return new PHPStan\Analyser\ExprHandler\BitwiseNotHandler(
			$this->getService('072'),
			$this->getService('0569'),
			$this->getService('0270')
		);
	}


	public function createService0239(): PHPStan\Analyser\ExprHandler\BooleanOrHandler
	{
		return new PHPStan\Analyser\ExprHandler\BooleanOrHandler($this->getService('0272'), $this->getService('0569'));
	}


	public function createService0240(): PHPStan\Analyser\ExprHandler\AssignOpHandler
	{
		return new PHPStan\Analyser\ExprHandler\AssignOpHandler(
			$this->getService('0208'),
			$this->getService('072'),
			$this->getService('0271'),
			$this->getService('0569'),
			$this->getService('0270'),
			$this->getService('0261')
		);
	}


	public function createService0241(): PHPStan\Analyser\ExprHandler\PropertyFetchHandler
	{
		return new PHPStan\Analyser\ExprHandler\PropertyFetchHandler(
			$this->getService('03'),
			$this->getService('0133'),
			$this->getService('0569'),
			$this->getService('0185'),
			$this->getService('0270')
		);
	}


	public function createService0242(): PHPStan\Analyser\ExprHandler\NullsafeMethodCallHandler
	{
		return new PHPStan\Analyser\ExprHandler\NullsafeMethodCallHandler(
			$this->getService('0264'),
			$this->getService('0569'),
			$this->getService('0270'),
			$this->getService('0272')
		);
	}


	public function createService0243(): PHPStan\Analyser\ExprHandler\ConstFetchHandler
	{
		return new PHPStan\Analyser\ExprHandler\ConstFetchHandler(
			$this->getService('0187'),
			$this->getService('0569'),
			$this->getService('0270'),
			$this->getService('reflectionProvider')
		);
	}


	public function createService0244(): PHPStan\Analyser\ExprHandler\IncludeHandler
	{
		return new PHPStan\Analyser\ExprHandler\IncludeHandler(
			$this->getService('0569'),
			$this->getService('0270'),
			$this->getService('010')
		);
	}


	public function createService0245(): PHPStan\Analyser\ExprHandler\TernaryHandler
	{
		return new PHPStan\Analyser\ExprHandler\TernaryHandler(
			$this->getService('0569'),
			$this->getService('0270'),
			$this->getService('0272')
		);
	}


	public function createService0246(): PHPStan\Analyser\ExprHandler\NewHandler
	{
		return new PHPStan\Analyser\ExprHandler\NewHandler(
			$this->getService('reflectionProvider'),
			$this->getService('phpstan.extensionsCollection.PHPStan.Type.DynamicStaticMethodThrowTypeExtension'),
			$this->getService('0553'),
			$this->getService('0133'),
			$this->getParameter('exceptions')['implicitThrows'],
			$this->getService('0569'),
			$this->getService('0270'),
			$this->getService('0263'),
			$this->getService('0333'),
			$this->getService('0281')
		);
	}


	public function createService0247(): PHPStan\Analyser\ExprHandler\ErrorSuppressHandler
	{
		return new PHPStan\Analyser\ExprHandler\ErrorSuppressHandler($this->getService('0569'));
	}


	public function createService0248(): PHPStan\Analyser\ExprHandler\VariableHandler
	{
		return new PHPStan\Analyser\ExprHandler\VariableHandler(
			$this->getService('0569'),
			$this->getService('0270'),
			$this->getService('0275'),
			$this->getService('072')
		);
	}


	public function createService0249(): PHPStan\Analyser\ExprHandler\CastStringHandler
	{
		return new PHPStan\Analyser\ExprHandler\CastStringHandler(
			$this->getService('072'),
			$this->getService('0271'),
			$this->getService('0569')
		);
	}


	public function createService0250(): PHPStan\Analyser\ExprHandler\FuncCallHandler
	{
		return new PHPStan\Analyser\ExprHandler\FuncCallHandler(
			$this->getService('reflectionProvider'),
			$this->getService('phpstan.extensionsCollection.PHPStan.Type.DynamicFunctionThrowTypeExtension'),
			$this->getService('0553'),
			$this->getParameter('exceptions')['implicitThrows'],
			$this->getParameter('rememberPossiblyImpureFunctionValues'),
			$this->getService('0267'),
			$this->getService('0569'),
			$this->getService('typeSpecifier'),
			$this->getService('0270'),
			$this->getService('0258'),
			$this->getService('0263'),
			$this->getService('0128'),
			$this->getService('0265'),
			$this->getService('0281'),
			$this->getService('0190'),
			$this->getService('0208'),
			$this->getService('0330')
		);
	}


	public function createService0251(): PHPStan\Analyser\ExprHandler\CloneHandler
	{
		return new PHPStan\Analyser\ExprHandler\CloneHandler($this->getService('0569'), $this->getService('0270'));
	}


	public function createService0252(): PHPStan\Analyser\ExprHandler\PostIncHandler
	{
		return new PHPStan\Analyser\ExprHandler\PostIncHandler(
			$this->getService('0569'),
			$this->getService('0270'),
			$this->getService('0268'),
			$this->getService('0208')
		);
	}


	public function createService0253(): PHPStan\Analyser\ExprHandler\MethodCallHandler
	{
		return new PHPStan\Analyser\ExprHandler\MethodCallHandler(
			$this->getService('0277'),
			$this->getService('0273'),
			$this->getService('0269'),
			$this->getService('reflectionProvider'),
			$this->getParameter('rememberPossiblyImpureFunctionValues'),
			$this->getService('0569'),
			$this->getService('typeSpecifier'),
			$this->getService('0270'),
			$this->getService('0263'),
			$this->getService('0258'),
			$this->getService('0281')
		);
	}


	public function createService0254(): PHPStan\Analyser\ExprHandler\EvalHandler
	{
		return new PHPStan\Analyser\ExprHandler\EvalHandler($this->getService('0569'), $this->getService('0270'));
	}


	public function createService0255(): PHPStan\Analyser\ExprHandler\BooleanNotHandler
	{
		return new PHPStan\Analyser\ExprHandler\BooleanNotHandler($this->getService('0569'), $this->getService('0270'));
	}


	public function createService0256(): PHPStan\Analyser\ExprHandler\InstanceofHandler
	{
		return new PHPStan\Analyser\ExprHandler\InstanceofHandler($this->getService('0569'), $this->getService('0270'));
	}


	public function createService0257(): PHPStan\Analyser\ExprHandler\Helper\OutputBufferHelper
	{
		return new PHPStan\Analyser\ExprHandler\Helper\OutputBufferHelper($this->getService('072'));
	}


	public function createService0258(): PHPStan\Analyser\ExprHandler\Helper\EarlyTerminatingCallHelper
	{
		return new PHPStan\Analyser\ExprHandler\Helper\EarlyTerminatingCallHelper(
			$this->getService('reflectionProvider'),
			$this->getParameter('earlyTerminatingMethodCalls'),
			$this->getParameter('earlyTerminatingFunctionCalls')
		);
	}


	public function createService0259(): PHPStan\Analyser\ExprHandler\Helper\CountNarrowingHelper
	{
		return new PHPStan\Analyser\ExprHandler\Helper\CountNarrowingHelper($this->getService('0270'), $this->getService('081'));
	}


	public function createService0260(): PHPStan\Analyser\ExprHandler\Helper\ForeachEmptyOrObjectNarrowingHelper
	{
		return new PHPStan\Analyser\ExprHandler\Helper\ForeachEmptyOrObjectNarrowingHelper(
			$this->getService('reflectionProvider'),
			$this->getService('0275'),
			$this->getService('0270'),
			$this->getService('0272')
		);
	}


	public function createService0261(): PHPStan\Analyser\ExprHandler\Helper\CoalesceCompositionHelper
	{
		return new PHPStan\Analyser\ExprHandler\Helper\CoalesceCompositionHelper($this->getService('0270'));
	}


	public function createService0262(): PHPStan\Analyser\ExprHandler\Helper\ConditionalExpressionHolderHelper
	{
		return new PHPStan\Analyser\ExprHandler\Helper\ConditionalExpressionHolderHelper($this->getService('0270'));
	}


	public function createService0263(): PHPStan\Analyser\ExprHandler\Helper\DynamicReturnTypeStoragePrimer
	{
		return new PHPStan\Analyser\ExprHandler\Helper\DynamicReturnTypeStoragePrimer;
	}


	public function createService0264(): PHPStan\Analyser\ExprHandler\Helper\NonNullabilityHelper
	{
		return new PHPStan\Analyser\ExprHandler\Helper\NonNullabilityHelper($this->getService('081'));
	}


	public function createService0265(): PHPStan\Analyser\ExprHandler\Helper\ClosureTypeResolver
	{
		return new PHPStan\Analyser\ExprHandler\Helper\ClosureTypeResolver(
			$this->getService('0193'),
			$this->getService('072'),
			$this->getService('0266'),
			$this->getService('0330')
		);
	}


	public function createService0266(): PHPStan\Analyser\ExprHandler\Helper\ContextualClosureParameterResolver
	{
		return new PHPStan\Analyser\ExprHandler\Helper\ContextualClosureParameterResolver(
			$this->getService('0193'),
			$this->getService('0330')
		);
	}


	public function createService0267(): PHPStan\Analyser\ExprHandler\Helper\FuncCallScopeEffectsHelper
	{
		return new PHPStan\Analyser\ExprHandler\Helper\FuncCallScopeEffectsHelper($this->getService('0257'), $this->getService('0208'));
	}


	public function createService0268(): PHPStan\Analyser\ExprHandler\Helper\IncDecTypeHelper
	{
		return new PHPStan\Analyser\ExprHandler\Helper\IncDecTypeHelper($this->getService('072'));
	}


	public function createService0269(): PHPStan\Analyser\ExprHandler\Helper\MethodThrowPointHelper
	{
		return new PHPStan\Analyser\ExprHandler\Helper\MethodThrowPointHelper(
			$this->getService('phpstan.extensionsCollection.PHPStan.Type.DynamicMethodThrowTypeExtension'),
			$this->getService('phpstan.extensionsCollection.PHPStan.Type.DynamicStaticMethodThrowTypeExtension'),
			$this->getParameter('exceptions')['implicitThrows']
		);
	}


	public function createService0270(): PHPStan\Analyser\ExprHandler\Helper\DefaultNarrowingHelper
	{
		return new PHPStan\Analyser\ExprHandler\Helper\DefaultNarrowingHelper(
			$this->getService('081'),
			$this->getParameter('rememberPossiblyImpureFunctionValues'),
			$this->getService('reflectionProvider')
		);
	}


	public function createService0271(): PHPStan\Analyser\ExprHandler\Helper\ImplicitToStringCallHelper
	{
		return new PHPStan\Analyser\ExprHandler\Helper\ImplicitToStringCallHelper(
			$this->getService('03'),
			$this->getService('0269'),
			$this->getService('0273'),
			$this->getService('0569')
		);
	}


	public function createService0272(): PHPStan\Analyser\ExprHandler\Helper\BooleanNarrowingHelper
	{
		return new PHPStan\Analyser\ExprHandler\Helper\BooleanNarrowingHelper($this->getService('0262'), $this->getService('0270'));
	}


	public function createService0273(): PHPStan\Analyser\ExprHandler\Helper\MethodCallReturnTypeHelper
	{
		return new PHPStan\Analyser\ExprHandler\Helper\MethodCallReturnTypeHelper($this->getService('0553'), $this->getService('0263'));
	}


	public function createService0274(): PHPStan\Analyser\ExprHandler\Helper\ClosureParameterResolver
	{
		return new PHPStan\Analyser\ExprHandler\Helper\ClosureParameterResolver(
			$this->getService('0193'),
			$this->getService('0265'),
			$this->getService('0266')
		);
	}


	public function createService0275(): PHPStan\Analyser\ExprHandler\Helper\IdenticalNarrowingHelper
	{
		return new PHPStan\Analyser\ExprHandler\Helper\IdenticalNarrowingHelper(
			$this->getService('0270'),
			$this->getService('reflectionProvider'),
			$this->getService('0259'),
			$this->getService('081'),
			$this->getService('0326')
		);
	}


	public function createService0276(): PHPStan\Analyser\ExprHandler\Helper\VirtualExprResultHelper
	{
		return new PHPStan\Analyser\ExprHandler\Helper\VirtualExprResultHelper($this->getService('0569'), $this->getService('0270'));
	}


	public function createService0277(): PHPStan\Analyser\CalledMethodProcessor
	{
		return new PHPStan\Analyser\CalledMethodProcessor(
			$this->getService('09'),
			$this->getService('defaultAnalysisParser'),
			$this->getService('0191')
		);
	}


	public function createService0278(): PHPStan\Analyser\Ignore\IgnoredErrorHelper
	{
		return new PHPStan\Analyser\Ignore\IgnoredErrorHelper(
			$this->getService('09'),
			$this->getParameter('ignoreErrors'),
			$this->getParameter('reportUnmatchedIgnoredErrors')
		);
	}


	public function createService0279(): PHPStan\Analyser\Ignore\IgnoreLexer
	{
		return new PHPStan\Analyser\Ignore\IgnoreLexer;
	}


	public function createService0280(): PHPStan\Analyser\StatementsHandler
	{
		return new PHPStan\Analyser\StatementsHandler(
			$this->getService('0556'),
			$this->getService('0328'),
			$this->getService('0327'),
			$this->getService('0333'),
			$this->getService('0329'),
			$this->getParameter('featureToggles')['unresolvedTemplateArguments']
		);
	}


	public function createService0281(): PHPStan\Analyser\ArgumentsHandler
	{
		return new PHPStan\Analyser\ArgumentsHandler(
			$this->getService('0328'),
			$this->getService('0569'),
			$this->getService('0190'),
			$this->getService('phpstan.extensionsCollection.PHPStan.Type.FunctionParameterOutTypeExtension'),
			$this->getService('phpstan.extensionsCollection.PHPStan.Type.MethodParameterOutTypeExtension'),
			$this->getService('phpstan.extensionsCollection.PHPStan.Type.StaticMethodParameterOutTypeExtension'),
			$this->getService('phpstan.extensionsCollection.PHPStan.Type.FunctionParameterClosureThisExtension'),
			$this->getService('phpstan.extensionsCollection.PHPStan.Type.MethodParameterClosureThisExtension'),
			$this->getService('phpstan.extensionsCollection.PHPStan.Type.StaticMethodParameterClosureThisExtension'),
			$this->getService('phpstan.extensionsCollection.PHPStan.Type.FunctionParameterClosureTypeExtension'),
			$this->getService('phpstan.extensionsCollection.PHPStan.Type.MethodParameterClosureTypeExtension'),
			$this->getService('phpstan.extensionsCollection.PHPStan.Type.StaticMethodParameterClosureTypeExtension'),
			$this->getParameter('exceptions')['implicitThrows'],
			$this->getService('0208'),
			$this->getService('0265'),
			$this->getService('0274')
		);
	}


	public function createService0282(): PHPStan\Analyser\ValueDependencyDeclarationDependencyTracker
	{
		return new PHPStan\Analyser\ValueDependencyDeclarationDependencyTracker($this->getService('0320'));
	}


	public function createService0283(): PHPStan\Analyser\StmtHandler\NopHandler
	{
		return new PHPStan\Analyser\StmtHandler\NopHandler;
	}


	public function createService0284(): PHPStan\Analyser\StmtHandler\NamespaceHandler
	{
		return new PHPStan\Analyser\StmtHandler\NamespaceHandler;
	}


	public function createService0285(): PHPStan\Analyser\StmtHandler\TraitHandler
	{
		return new PHPStan\Analyser\StmtHandler\TraitHandler($this->getService('reflectionProvider'));
	}


	public function createService0286(): PHPStan\Analyser\StmtHandler\LabelHandler
	{
		return new PHPStan\Analyser\StmtHandler\LabelHandler;
	}


	public function createService0287(): PHPStan\Analyser\StmtHandler\ExpressionHandler
	{
		return new PHPStan\Analyser\StmtHandler\ExpressionHandler(
			$this->getService('0280'),
			$this->getParameter('rememberPossiblyImpureFunctionValues')
		);
	}


	public function createService0288(): PHPStan\Analyser\StmtHandler\UnsetHandler
	{
		return new PHPStan\Analyser\StmtHandler\UnsetHandler($this->getService('0333'), $this->getService('0208'));
	}


	public function createService0289(): PHPStan\Analyser\StmtHandler\DeclareHandler
	{
		return new PHPStan\Analyser\StmtHandler\DeclareHandler;
	}


	public function createService0290(): PHPStan\Analyser\StmtHandler\ReturnHandler
	{
		return new PHPStan\Analyser\StmtHandler\ReturnHandler($this->getService('0280'));
	}


	public function createService0291(): PHPStan\Analyser\StmtHandler\EnumCaseHandler
	{
		return new PHPStan\Analyser\StmtHandler\EnumCaseHandler($this->getService('0194'));
	}


	public function createService0292(): PHPStan\Analyser\StmtHandler\FunctionHandler
	{
		return new PHPStan\Analyser\StmtHandler\FunctionHandler(
			$this->getService('0318'),
			$this->getService('0186'),
			$this->getService('0194'),
			$this->getService('0319')
		);
	}


	public function createService0293(): PHPStan\Analyser\StmtHandler\PropertyHandler
	{
		return new PHPStan\Analyser\StmtHandler\PropertyHandler(
			$this->getService('0186'),
			$this->getService('0192'),
			$this->getService('0194')
		);
	}


	public function createService0294(): PHPStan\Analyser\StmtHandler\ClassConstHandler
	{
		return new PHPStan\Analyser\StmtHandler\ClassConstHandler($this->getService('0194'));
	}


	public function createService0295(): PHPStan\Analyser\StmtHandler\ForHandler
	{
		return new PHPStan\Analyser\StmtHandler\ForHandler(
			$this->getParameter('polluteScopeWithLoopInitialAssignments'),
			$this->getParameter('treatPhpDocTypesAsCertain')
		);
	}


	public function createService0296(): PHPStan\Analyser\StmtHandler\ForeachHandler
	{
		return new PHPStan\Analyser\StmtHandler\ForeachHandler(
			$this->getService('0333'),
			$this->getParameter('exceptions')['implicitThrows'],
			$this->getService('0315'),
			$this->getService('0208'),
			$this->getParameter('polluteScopeWithAlwaysIterableForeach')
		);
	}


	public function createService0297(): PHPStan\Analyser\StmtHandler\DoWhileHandler
	{
		return new PHPStan\Analyser\StmtHandler\DoWhileHandler($this->getParameter('treatPhpDocTypesAsCertain'));
	}


	public function createService0298(): PHPStan\Analyser\StmtHandler\GroupUseHandler
	{
		return new PHPStan\Analyser\StmtHandler\GroupUseHandler;
	}


	public function createService0299(): PHPStan\Analyser\StmtHandler\BreakContinueHandler
	{
		return new PHPStan\Analyser\StmtHandler\BreakContinueHandler;
	}


	public function createService0300(): PHPStan\Analyser\StmtHandler\BlockHandler
	{
		return new PHPStan\Analyser\StmtHandler\BlockHandler($this->getParameter('polluteScopeWithBlock'));
	}


	public function createService0301(): PHPStan\Analyser\StmtHandler\ConstHandler
	{
		return new PHPStan\Analyser\StmtHandler\ConstHandler;
	}


	public function createService0302(): PHPStan\Analyser\StmtHandler\SwitchHandler
	{
		return new PHPStan\Analyser\StmtHandler\SwitchHandler($this->getService('0333'));
	}


	public function createService0303(): PHPStan\Analyser\StmtHandler\GotoHandler
	{
		return new PHPStan\Analyser\StmtHandler\GotoHandler;
	}


	public function createService0304(): PHPStan\Analyser\StmtHandler\UseHandler
	{
		return new PHPStan\Analyser\StmtHandler\UseHandler;
	}


	public function createService0305(): PHPStan\Analyser\StmtHandler\IfHandler
	{
		return new PHPStan\Analyser\StmtHandler\IfHandler($this->getParameter('treatPhpDocTypesAsCertain'));
	}


	public function createService0306(): PHPStan\Analyser\StmtHandler\ClassLikeHandler
	{
		return new PHPStan\Analyser\StmtHandler\ClassLikeHandler(
			$this->getService('betterReflectionReflector'),
			$this->getService('0568'),
			$this->getService('0277'),
			$this->getService('reflectionProvider'),
			$this->getService('0194'),
			$this->getService('phpstan.extensionsCollection.PHPStan.Rules.Properties.ReadWritePropertiesExtension')
		);
	}


	public function createService0307(): PHPStan\Analyser\StmtHandler\TryCatchHandler
	{
		return new PHPStan\Analyser\StmtHandler\TryCatchHandler($this->getService('0280'));
	}


	public function createService0308(): PHPStan\Analyser\StmtHandler\ClassMethodHandler
	{
		return new PHPStan\Analyser\StmtHandler\ClassMethodHandler(
			$this->getService('0318'),
			$this->getService('0186'),
			$this->getService('0192'),
			$this->getService('0194'),
			$this->getService('0319')
		);
	}


	public function createService0309(): PHPStan\Analyser\StmtHandler\EchoHandler
	{
		return new PHPStan\Analyser\StmtHandler\EchoHandler($this->getService('0271'));
	}


	public function createService0310(): PHPStan\Analyser\StmtHandler\WhileHandler
	{
		return new PHPStan\Analyser\StmtHandler\WhileHandler(
			$this->getParameter('polluteScopeWithLoopInitialAssignments'),
			$this->getParameter('treatPhpDocTypesAsCertain')
		);
	}


	public function createService0311(): PHPStan\Analyser\StmtHandler\GlobalHandler
	{
		return new PHPStan\Analyser\StmtHandler\GlobalHandler($this->getService('0315'));
	}


	public function createService0312(): PHPStan\Analyser\StmtHandler\StaticVariableHandler
	{
		return new PHPStan\Analyser\StmtHandler\StaticVariableHandler($this->getService('0315'), $this->getService('0329'));
	}


	public function createService0313(): PHPStan\Analyser\StmtHandler\InlineHtmlHandler
	{
		return new PHPStan\Analyser\StmtHandler\InlineHtmlHandler;
	}


	public function createService0314(): PHPStan\Analyser\StmtHandler\TraitUseHandler
	{
		return new PHPStan\Analyser\StmtHandler\TraitUseHandler(
			$this->getService('reflectionProvider'),
			$this->getService('09'),
			$this->getService('defaultAnalysisParser'),
			$this->getService('0194'),
			$this->getService('0556')
		);
	}


	public function createService0315(): PHPStan\Analyser\VarAnnotationProcessor
	{
		return new PHPStan\Analyser\VarAnnotationProcessor($this->getService('0556'));
	}


	public function createService0316(): PHPStan\Analyser\AnalyserResultFinalizer
	{
		return new PHPStan\Analyser\AnalyserResultFinalizer(
			$this->getService('registry'),
			$this->getService('phpstan.extensionsCollection.PHPStan.Analyser.IgnoreErrorExtension'),
			$this->getService('0189'),
			$this->getService('0191'),
			$this->getService('0317'),
			$this->getParameter('reportUnmatchedIgnoredErrors'),
			$this->getService('0320'),
			$this->getParameter('featureToggles')['reuseCollectedDataRuleErrors']
		);
	}


	public function createService0317(): PHPStan\Analyser\LocalIgnoresProcessor
	{
		return new PHPStan\Analyser\LocalIgnoresProcessor;
	}


	public function createService0318(): PHPStan\Analyser\DeprecatedAttributeResolver
	{
		return new PHPStan\Analyser\DeprecatedAttributeResolver($this->getService('072'));
	}


	public function createService0319(): PHPStan\Analyser\ParametersProcessor
	{
		return new PHPStan\Analyser\ParametersProcessor($this->getService('0194'));
	}


	public function createService0320(): PHPStan\Analyser\ValueDependencyCollector
	{
		return new PHPStan\Analyser\ValueDependencyCollector(
			$this->getService('phpstan.extensionsCollection.PHPStan.Analyser.ResultCache.ResultCacheValueExtension'),
			$this->getService('09')
		);
	}


	public function createService0321(): PHPStan\Analyser\FileAnalyser
	{
		return new PHPStan\Analyser\FileAnalyser(
			$this->getService('0191'),
			$this->getService('0193'),
			$this->getService('defaultAnalysisParser'),
			$this->getService('phpstan.extensionsCollection.PHPStan.Analyser.IgnoreErrorExtension'),
			$this->getService('0189'),
			$this->getService('0317'),
			$this->getParameter('reportIgnoresWithoutComments'),
			$this->getService('0320'),
			$this->getService('033')
		);
	}


	public function createService0322(): PHPStan\Analyser\ResultCache\ClassResultCacheValueExtension
	{
		return new PHPStan\Analyser\ResultCache\ClassResultCacheValueExtension(
			$this->getService('reflectionProvider'),
			$this->getService('034'),
			$this->getService('07')
		);
	}


	public function createService0323(): PHPStan\Analyser\ResultCache\ResultCacheClearer
	{
		return new PHPStan\Analyser\ResultCache\ResultCacheClearer($this->getParameter('resultCachePath'));
	}


	public function createService0324(): PHPStan\Analyser\ResultCache\FileResultCacheValueExtension
	{
		return new PHPStan\Analyser\ResultCache\FileResultCacheValueExtension($this->getService('07'), $this->getParameter('rootDir'));
	}


	public function createService0325(): PHPStan\Analyser\ResultCache\DirectoryResultCacheValueExtension
	{
		return new PHPStan\Analyser\ResultCache\DirectoryResultCacheValueExtension(
			$this->getService('07'),
			$this->getParameter('rootDir')
		);
	}


	public function createService0326(): PHPStan\Analyser\RicherScopeGetTypeHelper
	{
		return new PHPStan\Analyser\RicherScopeGetTypeHelper($this->getService('072'), $this->getService('0133'));
	}


	public function createService0327(): PHPStan\Analyser\Generics\TemplateArgumentResolver
	{
		return new PHPStan\Analyser\Generics\TemplateArgumentResolver;
	}


	public function createService0328(): PHPStan\Analyser\Generics\TemplateArgumentObserver
	{
		return new PHPStan\Analyser\Generics\TemplateArgumentObserver;
	}


	public function createService0329(): PHPStan\Analyser\Generics\StaticVariableInference
	{
		return new PHPStan\Analyser\Generics\StaticVariableInference(
			$this->getService('reflectionProvider'),
			$this->getParameter('featureToggles')['staticVariablesFromUsages']
		);
	}


	public function createService0330(): PHPStan\Analyser\Generics\ClosureSignatureInference
	{
		return new PHPStan\Analyser\Generics\ClosureSignatureInference($this->getParameter('featureToggles')['closureSignaturesFromUsages']);
	}


	public function createService0331(): PHPStan\DependencyInjection\Reflection\LazyClassReflectionExtensionRegistryProvider
	{
		return new PHPStan\DependencyInjection\Reflection\LazyClassReflectionExtensionRegistryProvider($this->getService('0333'));
	}


	public function createService0332(): PHPStan\DependencyInjection\DerivativeContainerFactory
	{
		return new PHPStan\DependencyInjection\DerivativeContainerFactory(
			$this->getParameter('currentWorkingDirectory'),
			$this->getParameter('tempDir'),
			$this->getParameter('additionalConfigFiles'),
			$this->getParameter('analysedPaths'),
			$this->getParameter('composerAutoloaderProjectPaths'),
			$this->getParameter('analysedPathsFromConfig'),
			$this->getParameter('usedLevel'),
			$this->getParameter('generateBaselineFile'),
			$this->getParameter('cliAutoloadFile'),
			$this->getParameter('singleReflectionFile'),
			$this->getParameter('singleReflectionInsteadOfFile')
		);
	}


	public function createService0333(): PHPStan\DependencyInjection\MemoizingContainer
	{
		return new PHPStan\DependencyInjection\MemoizingContainer($this->getService('0334'));
	}


	public function createService0334(): PHPStan\DependencyInjection\Nette\NetteContainer
	{
		return new PHPStan\DependencyInjection\Nette\NetteContainer($this);
	}


	public function createService0335(): PHPStan\Collectors\RegistryFactory
	{
		return new PHPStan\Collectors\RegistryFactory($this->getService('phpstan.extensionsCollection.PHPStan.Collectors.Collector'));
	}


	public function createService0336(): PHPStan\Collectors\Registry
	{
		return $this->getService('0335')->create();
	}


	public function createService0337(): PHPStan\Fixable\PhpDoc\PhpDocEditor
	{
		return new PHPStan\Fixable\PhpDoc\PhpDocEditor($this->getService('0894'), $this->getService('0890'), $this->getService('0893'));
	}


	public function createService0338(): PHPStan\Fixable\Patcher
	{
		return new PHPStan\Fixable\Patcher;
	}


	public function createService0339(): PHPStan\Command\ErrorFormatter\CiDetectedErrorFormatter
	{
		return new PHPStan\Command\ErrorFormatter\CiDetectedErrorFormatter(
			$this->getService('errorFormatter.github'),
			$this->getService('errorFormatter.teamcity')
		);
	}


	public function createService0340(): PHPStan\Command\AnalyseApplication
	{
		return new PHPStan\Command\AnalyseApplication(
			$this->getService('0342'),
			$this->getService('0343'),
			$this->getService('0316'),
			$this->getService('023'),
			$this->getService('0571'),
			$this->getService('0278'),
			$this->getService('017')
		);
	}


	public function createService0341(): PHPStan\Command\FixerWorkerRunner
	{
		return new PHPStan\Command\FixerWorkerRunner(
			$this->getService('0278'),
			$this->getService('0571'),
			$this->getService('0316'),
			$this->getService('036'),
			$this->getService('039'),
			$this->getService('041')
		);
	}


	public function createService0342(): PHPStan\Command\BootstrapFilesRunner
	{
		return new PHPStan\Command\BootstrapFilesRunner($this->getService('0333'), $this->getService('064'));
	}


	public function createService0343(): PHPStan\Command\AnalyserRunner
	{
		return new PHPStan\Command\AnalyserRunner(
			$this->getService('039'),
			$this->getService('0188'),
			$this->getService('036'),
			$this->getService('041'),
			$this->getService('0342')
		);
	}


	public function createService0344(): PHPStan\Command\FixerApplication
	{
		return new PHPStan\Command\FixerApplication(
			$this->getService('06'),
			$this->getService('0278'),
			$this->getService('017'),
			$this->getParameter('analysedPaths'),
			$this->getParameter('currentWorkingDirectory'),
			$this->getParameter('pro')['tmpDir'],
			$this->getParameter('composerAutoloaderProjectPaths'),
			$this->getParameter('allConfigFiles'),
			$this->getParameter('cliAutoloadFile'),
			$this->getParameter('bootstrapFiles'),
			$this->getParameter('editorUrl'),
			$this->getParameter('usedLevel'),
			$this->getService('0184'),
			$this->getService('038'),
			$this->getService('0341')
		);
	}


	public function createService0345(): PHPStan\Type\Regex\RegexExpressionHelper
	{
		return new PHPStan\Type\Regex\RegexExpressionHelper($this->getService('072'));
	}


	public function createService0346(): PHPStan\Type\Regex\RegexGroupParser
	{
		return new PHPStan\Type\Regex\RegexGroupParser($this->getService('03'), $this->getService('0345'));
	}


	public function createService0347(): PHPStan\Type\Php\StrvalFamilyFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\StrvalFamilyFunctionReturnTypeExtension;
	}


	public function createService0348(): PHPStan\Type\Php\IntdivThrowTypeExtension
	{
		return new PHPStan\Type\Php\IntdivThrowTypeExtension;
	}


	public function createService0349(): PHPStan\Type\Php\DateTimeModifyMethodThrowTypeExtension
	{
		return new PHPStan\Type\Php\DateTimeModifyMethodThrowTypeExtension;
	}


	public function createService0350(): PHPStan\Type\Php\DateFormatMethodReturnTypeExtension
	{
		return new PHPStan\Type\Php\DateFormatMethodReturnTypeExtension($this->getService('0456'));
	}


	public function createService0351(): PHPStan\Type\Php\ArrayNextDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayNextDynamicReturnTypeExtension;
	}


	public function createService0352(): PHPStan\Type\Php\PrintfFormatParser
	{
		return new PHPStan\Type\Php\PrintfFormatParser;
	}


	public function createService0353(): PHPStan\Type\Php\IsIterableFunctionTypeSpecifyingExtension
	{
		return new PHPStan\Type\Php\IsIterableFunctionTypeSpecifyingExtension;
	}


	public function createService0354(): PHPStan\Type\Php\ClosureGetCurrentDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\ClosureGetCurrentDynamicReturnTypeExtension;
	}


	public function createService0355(): PHPStan\Type\Php\ArrayIntersectKeyFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayIntersectKeyFunctionReturnTypeExtension;
	}


	public function createService0356(): PHPStan\Type\Php\HashFunctionsReturnTypeExtension
	{
		return new PHPStan\Type\Php\HashFunctionsReturnTypeExtension;
	}


	public function createService0357(): PHPStan\Type\Php\Base64DecodeDynamicFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\Base64DecodeDynamicFunctionReturnTypeExtension;
	}


	public function createService0358(): PHPStan\Type\Php\InArrayFunctionTypeSpecifyingExtension
	{
		return new PHPStan\Type\Php\InArrayFunctionTypeSpecifyingExtension;
	}


	public function createService0359(): PHPStan\Type\Php\CompactFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\CompactFunctionReturnTypeExtension($this->getParameter('checkMaybeUndefinedVariables'));
	}


	public function createService0360(): PHPStan\Type\Php\AbsFunctionDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\AbsFunctionDynamicReturnTypeExtension;
	}


	public function createService0361(): PHPStan\Type\Php\DsMapDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\DsMapDynamicReturnTypeExtension;
	}


	public function createService0362(): PHPStan\Type\Php\DateTimeConstructorThrowTypeExtension
	{
		return new PHPStan\Type\Php\DateTimeConstructorThrowTypeExtension;
	}


	public function createService0363(): PHPStan\Type\Php\ClassExistsFunctionTypeSpecifyingExtension
	{
		return new PHPStan\Type\Php\ClassExistsFunctionTypeSpecifyingExtension($this->getService('reflectionProvider'));
	}


	public function createService0364(): PHPStan\Type\Php\IsArrayFunctionTypeSpecifyingExtension
	{
		return new PHPStan\Type\Php\IsArrayFunctionTypeSpecifyingExtension;
	}


	public function createService0365(): PHPStan\Type\Php\StrSplitFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\StrSplitFunctionReturnTypeExtension;
	}


	public function createService0366(): PHPStan\Type\Php\ReflectionClassIsSubclassOfTypeSpecifyingExtension
	{
		return new PHPStan\Type\Php\ReflectionClassIsSubclassOfTypeSpecifyingExtension;
	}


	public function createService0367(): PHPStan\Type\Php\ArrayReverseFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayReverseFunctionReturnTypeExtension;
	}


	public function createService0368(): PHPStan\Type\Php\ArrayCombineFunctionThrowTypeExtension
	{
		return new PHPStan\Type\Php\ArrayCombineFunctionThrowTypeExtension($this->getService('0534'));
	}


	public function createService0369(): PHPStan\Type\Php\ArrayFillKeysFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayFillKeysFunctionReturnTypeExtension;
	}


	public function createService0370(): PHPStan\Type\Php\ConstantHelper
	{
		return new PHPStan\Type\Php\ConstantHelper;
	}


	public function createService0371(): PHPStan\Type\Php\DateIntervalFormatReturnTypeHelper
	{
		return new PHPStan\Type\Php\DateIntervalFormatReturnTypeHelper;
	}


	public function createService0372(): PHPStan\Type\Php\StrlenFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\StrlenFunctionReturnTypeExtension;
	}


	public function createService0373(): PHPStan\Type\Php\DomDocumentCreateElementDynamicThrowTypeExtension
	{
		return new PHPStan\Type\Php\DomDocumentCreateElementDynamicThrowTypeExtension;
	}


	public function createService0374(): PHPStan\Type\Php\SimpleXMLElementAsXMLMethodReturnTypeExtension
	{
		return new PHPStan\Type\Php\SimpleXMLElementAsXMLMethodReturnTypeExtension;
	}


	public function createService0375(): PHPStan\Type\Php\OpenSslCipherMethodsProvider
	{
		return new PHPStan\Type\Php\OpenSslCipherMethodsProvider;
	}


	public function createService0376(): PHPStan\Type\Php\ArrayColumnHelper
	{
		return new PHPStan\Type\Php\ArrayColumnHelper;
	}


	public function createService0377(): PHPStan\Type\Php\LtrimFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\LtrimFunctionReturnTypeExtension;
	}


	public function createService0378(): PHPStan\Type\Php\GettypeFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\GettypeFunctionReturnTypeExtension;
	}


	public function createService0379(): PHPStan\Type\Php\DateTimeSubMethodThrowTypeExtension
	{
		return new PHPStan\Type\Php\DateTimeSubMethodThrowTypeExtension;
	}


	public function createService0380(): PHPStan\Type\Php\JsonThrowOnErrorDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\JsonThrowOnErrorDynamicReturnTypeExtension(
			$this->getService('reflectionProvider'),
			$this->getService('0557')
		);
	}


	public function createService0381(): PHPStan\Type\Php\ReflectionMethodConstructorThrowTypeExtension
	{
		return new PHPStan\Type\Php\ReflectionMethodConstructorThrowTypeExtension($this->getService('reflectionProvider'));
	}


	public function createService0382(): PHPStan\Type\Php\ClosureBindToDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\ClosureBindToDynamicReturnTypeExtension;
	}


	public function createService0383(): PHPStan\Type\Php\DateTimeDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\DateTimeDynamicReturnTypeExtension;
	}


	public function createService0384(): PHPStan\Type\Php\IdateFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\IdateFunctionReturnTypeExtension($this->getService('0481'));
	}


	public function createService0385(): PHPStan\Type\Php\ExplodeFunctionDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\ExplodeFunctionDynamicReturnTypeExtension;
	}


	public function createService0386(): PHPStan\Type\Php\RegexArrayShapeMatcher
	{
		return new PHPStan\Type\Php\RegexArrayShapeMatcher(
			$this->getService('0346'),
			$this->getService('0345'),
			$this->getService('03')
		);
	}


	public function createService0387(): PHPStan\Type\Php\IsAFunctionTypeSpecifyingExtension
	{
		return new PHPStan\Type\Php\IsAFunctionTypeSpecifyingExtension($this->getService('0439'));
	}


	public function createService0388(): PHPStan\Type\Php\ParseUrlFunctionDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\ParseUrlFunctionDynamicReturnTypeExtension;
	}


	public function createService0389(): PHPStan\Type\Php\CountFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\CountFunctionReturnTypeExtension;
	}


	public function createService0390(): PHPStan\Type\Php\DomDocumentCreateElementDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\DomDocumentCreateElementDynamicReturnTypeExtension;
	}


	public function createService0391(): PHPStan\Type\Php\ArrayPointerFunctionsDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayPointerFunctionsDynamicReturnTypeExtension;
	}


	public function createService0392(): PHPStan\Type\Php\IniGetReturnTypeExtension
	{
		return new PHPStan\Type\Php\IniGetReturnTypeExtension;
	}


	public function createService0393(): PHPStan\Type\Php\PregFilterFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\PregFilterFunctionReturnTypeExtension;
	}


	public function createService0394(): PHPStan\Type\Php\GetClassFunctionThrowTypeExtension
	{
		return new PHPStan\Type\Php\GetClassFunctionThrowTypeExtension;
	}


	public function createService0395(): PHPStan\Type\Php\ArrayCountValuesDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayCountValuesDynamicReturnTypeExtension;
	}


	public function createService0396(): PHPStan\Type\Php\FilterInputDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\FilterInputDynamicReturnTypeExtension($this->getService('0545'));
	}


	public function createService0397(): PHPStan\Type\Php\StrWordCountFunctionDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\StrWordCountFunctionDynamicReturnTypeExtension;
	}


	public function createService0398(): PHPStan\Type\Php\SimpleXMLElementClassPropertyReflectionExtension
	{
		return new PHPStan\Type\Php\SimpleXMLElementClassPropertyReflectionExtension;
	}


	public function createService0399(): PHPStan\Type\Php\LocaltimeFunctionDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\LocaltimeFunctionDynamicReturnTypeExtension;
	}


	public function createService0400(): PHPStan\Type\Php\PregMatchTypeSpecifyingExtension
	{
		return new PHPStan\Type\Php\PregMatchTypeSpecifyingExtension($this->getService('0386'));
	}


	public function createService0401(): PHPStan\Type\Php\DateIntervalFormatFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\DateIntervalFormatFunctionReturnTypeExtension($this->getService('0371'));
	}


	public function createService0402(): PHPStan\Type\Php\ArrayReplaceFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayReplaceFunctionReturnTypeExtension;
	}


	public function createService0403(): PHPStan\Type\Php\ThrowableReturnTypeExtension
	{
		return new PHPStan\Type\Php\ThrowableReturnTypeExtension;
	}


	public function createService0404(): PHPStan\Type\Php\JsonThrowTypeExtension
	{
		return new PHPStan\Type\Php\JsonThrowTypeExtension($this->getService('reflectionProvider'), $this->getService('0557'));
	}


	public function createService0405(): PHPStan\Type\Php\FilterFunctionFlagsHelper
	{
		return new PHPStan\Type\Php\FilterFunctionFlagsHelper;
	}


	public function createService0406(): PHPStan\Type\Php\MinMaxFunctionThrowTypeExtension
	{
		return new PHPStan\Type\Php\MinMaxFunctionThrowTypeExtension;
	}


	public function createService0407(): PHPStan\Type\Php\StrSplitFunctionThrowTypeExtension
	{
		return new PHPStan\Type\Php\StrSplitFunctionThrowTypeExtension;
	}


	public function createService0408(): PHPStan\Type\Php\ClosureBindDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\ClosureBindDynamicReturnTypeExtension;
	}


	public function createService0409(): PHPStan\Type\Php\XMLReaderOpenReturnTypeExtension
	{
		return new PHPStan\Type\Php\XMLReaderOpenReturnTypeExtension;
	}


	public function createService0410(): PHPStan\Type\Php\ArraySearchFunctionDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArraySearchFunctionDynamicReturnTypeExtension;
	}


	public function createService0411(): PHPStan\Type\Php\ArrayFirstLastDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayFirstLastDynamicReturnTypeExtension;
	}


	public function createService0412(): PHPStan\Type\Php\BcMathNumberOperatorTypeSpecifyingExtension
	{
		return new PHPStan\Type\Php\BcMathNumberOperatorTypeSpecifyingExtension($this->getService('03'));
	}


	public function createService0413(): PHPStan\Type\Php\BcMathStringOrNullReturnTypeExtension
	{
		return new PHPStan\Type\Php\BcMathStringOrNullReturnTypeExtension;
	}


	public function createService0414(): PHPStan\Type\Php\PregReplaceCallbackClosureTypeExtension
	{
		return new PHPStan\Type\Php\PregReplaceCallbackClosureTypeExtension($this->getService('0386'));
	}


	public function createService0415(): PHPStan\Type\Php\SprintfFunctionDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\SprintfFunctionDynamicReturnTypeExtension;
	}


	public function createService0416(): PHPStan\Type\Php\ClosureFromCallableDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\ClosureFromCallableDynamicReturnTypeExtension;
	}


	public function createService0417(): PHPStan\Type\Php\MethodExistsTypeSpecifyingExtension
	{
		return new PHPStan\Type\Php\MethodExistsTypeSpecifyingExtension;
	}


	public function createService0418(): PHPStan\Type\Php\ArrayFilterFunctionReturnTypeHelper
	{
		return new PHPStan\Type\Php\ArrayFilterFunctionReturnTypeHelper($this->getService('reflectionProvider'));
	}


	public function createService0419(): PHPStan\Type\Php\ArrayKeyDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayKeyDynamicReturnTypeExtension;
	}


	public function createService0420(): PHPStan\Type\Php\ReflectionClassConstantConstructorThrowTypeExtension
	{
		return new PHPStan\Type\Php\ReflectionClassConstantConstructorThrowTypeExtension($this->getService('reflectionProvider'));
	}


	public function createService0421(): PHPStan\Type\Php\VersionCompareFunctionDynamicThrowTypeExtension
	{
		return new PHPStan\Type\Php\VersionCompareFunctionDynamicThrowTypeExtension;
	}


	public function createService0422(): PHPStan\Type\Php\AssertFunctionTypeSpecifyingExtension
	{
		return new PHPStan\Type\Php\AssertFunctionTypeSpecifyingExtension;
	}


	public function createService0423(): PHPStan\Type\Php\MbStrlenFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\MbStrlenFunctionReturnTypeExtension;
	}


	public function createService0424(): PHPStan\Type\Php\ArraySearchFunctionTypeSpecifyingExtension
	{
		return new PHPStan\Type\Php\ArraySearchFunctionTypeSpecifyingExtension;
	}


	public function createService0425(): PHPStan\Type\Php\ArrayMergeFunctionDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayMergeFunctionDynamicReturnTypeExtension;
	}


	public function createService0426(): PHPStan\Type\Php\DefinedConstantTypeSpecifyingExtension
	{
		return new PHPStan\Type\Php\DefinedConstantTypeSpecifyingExtension($this->getService('0370'));
	}


	public function createService0427(): PHPStan\Type\Php\VersionCompareFunctionDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\VersionCompareFunctionDynamicReturnTypeExtension($this->getService('02'));
	}


	public function createService0428(): PHPStan\Type\Php\ClassImplementsFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\ClassImplementsFunctionReturnTypeExtension;
	}


	public function createService0429(): PHPStan\Type\Php\ArraySpliceFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArraySpliceFunctionReturnTypeExtension;
	}


	public function createService0430(): PHPStan\Type\Php\StrtotimeFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\StrtotimeFunctionReturnTypeExtension;
	}


	public function createService0431(): PHPStan\Type\Php\FilterFunctionsThrowTypeExtension
	{
		return new PHPStan\Type\Php\FilterFunctionsThrowTypeExtension(
			$this->getService('reflectionProvider'),
			$this->getService('0545'),
			$this->getService('0405')
		);
	}


	public function createService0432(): PHPStan\Type\Php\MinMaxFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\MinMaxFunctionReturnTypeExtension;
	}


	public function createService0433(): PHPStan\Type\Php\PowFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\PowFunctionReturnTypeExtension;
	}


	public function createService0434(): PHPStan\Type\Php\AssertThrowTypeExtension
	{
		return new PHPStan\Type\Php\AssertThrowTypeExtension;
	}


	public function createService0435(): PHPStan\Type\Php\DefineConstantTypeSpecifyingExtension
	{
		return new PHPStan\Type\Php\DefineConstantTypeSpecifyingExtension;
	}


	public function createService0436(): PHPStan\Type\Php\NonEmptyStringFunctionsReturnTypeExtension
	{
		return new PHPStan\Type\Php\NonEmptyStringFunctionsReturnTypeExtension;
	}


	public function createService0437(): PHPStan\Type\Php\IteratorToArrayFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\IteratorToArrayFunctionReturnTypeExtension;
	}


	public function createService0438(): PHPStan\Type\Php\PregSplitDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\PregSplitDynamicReturnTypeExtension($this->getService('0557'));
	}


	public function createService0439(): PHPStan\Type\Php\IsAFunctionTypeSpecifyingHelper
	{
		return new PHPStan\Type\Php\IsAFunctionTypeSpecifyingHelper;
	}


	public function createService0440(): PHPStan\Type\Php\ArgumentBasedFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArgumentBasedFunctionReturnTypeExtension;
	}


	public function createService0441(): PHPStan\Type\Php\DateIntervalDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\DateIntervalDynamicReturnTypeExtension;
	}


	public function createService0442(): PHPStan\Type\Php\ArrayFilterFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayFilterFunctionReturnTypeExtension($this->getService('0418'));
	}


	public function createService0443(): PHPStan\Type\Php\ArrayFindParameterClosureTypeExtension
	{
		return new PHPStan\Type\Php\ArrayFindParameterClosureTypeExtension;
	}


	public function createService0444(): PHPStan\Type\Php\FilterVarDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\FilterVarDynamicReturnTypeExtension($this->getService('0545'));
	}


	public function createService0445(): PHPStan\Type\Php\SscanfFunctionDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\SscanfFunctionDynamicReturnTypeExtension;
	}


	public function createService0446(): PHPStan\Type\Php\StrPadFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\StrPadFunctionReturnTypeExtension;
	}


	public function createService0447(): PHPStan\Type\Php\GetCalledClassDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\GetCalledClassDynamicReturnTypeExtension;
	}


	public function createService0448(): PHPStan\Type\Php\StrShuffleFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\StrShuffleFunctionReturnTypeExtension;
	}


	public function createService0449(): PHPStan\Type\Php\SubstrDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\SubstrDynamicReturnTypeExtension;
	}


	public function createService0450(): PHPStan\Type\Php\ParseStrParameterOutTypeExtension
	{
		return new PHPStan\Type\Php\ParseStrParameterOutTypeExtension;
	}


	public function createService0451(): PHPStan\Type\Php\ReflectionClassConstructorThrowTypeExtension
	{
		return new PHPStan\Type\Php\ReflectionClassConstructorThrowTypeExtension;
	}


	public function createService0452(): PHPStan\Type\Php\OpenSslEncryptParameterOutTypeExtension
	{
		return new PHPStan\Type\Php\OpenSslEncryptParameterOutTypeExtension($this->getService('0375'));
	}


	public function createService0453(): PHPStan\Type\Php\IsCallableFunctionTypeSpecifyingExtension
	{
		return new PHPStan\Type\Php\IsCallableFunctionTypeSpecifyingExtension($this->getService('0417'));
	}


	public function createService0454(): PHPStan\Type\Php\DsMapDynamicMethodThrowTypeExtension
	{
		return new PHPStan\Type\Php\DsMapDynamicMethodThrowTypeExtension;
	}


	public function createService0455(): PHPStan\Type\Php\RandomizerMethodReturnTypeExtension
	{
		return new PHPStan\Type\Php\RandomizerMethodReturnTypeExtension;
	}


	public function createService0456(): PHPStan\Type\Php\DateFunctionReturnTypeHelper
	{
		return new PHPStan\Type\Php\DateFunctionReturnTypeHelper;
	}


	public function createService0457(): PHPStan\Type\Php\RandomIntFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\RandomIntFunctionReturnTypeExtension;
	}


	public function createService0458(): PHPStan\Type\Php\ArrayPopFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayPopFunctionReturnTypeExtension;
	}


	public function createService0459(): PHPStan\Type\Php\TriggerErrorDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\TriggerErrorDynamicReturnTypeExtension;
	}


	public function createService0460(): PHPStan\Type\Php\NumberFormatFunctionDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\NumberFormatFunctionDynamicReturnTypeExtension;
	}


	public function createService0461(): PHPStan\Type\Php\ArrayChangeKeyCaseFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayChangeKeyCaseFunctionReturnTypeExtension;
	}


	public function createService0462(): PHPStan\Type\Php\StrrevFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\StrrevFunctionReturnTypeExtension;
	}


	public function createService0463(): PHPStan\Type\Php\ArrayCurrentDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayCurrentDynamicReturnTypeExtension;
	}


	public function createService0464(): PHPStan\Type\Php\ArraySliceFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArraySliceFunctionReturnTypeExtension;
	}


	public function createService0465(): PHPStan\Type\Php\DateIntervalConstructorThrowTypeExtension
	{
		return new PHPStan\Type\Php\DateIntervalConstructorThrowTypeExtension;
	}


	public function createService0466(): PHPStan\Type\Php\PgDmlDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\PgDmlDynamicReturnTypeExtension($this->getService('0557'));
	}


	public function createService0467(): PHPStan\Type\Php\IsSubclassOfFunctionTypeSpecifyingExtension
	{
		return new PHPStan\Type\Php\IsSubclassOfFunctionTypeSpecifyingExtension($this->getService('0439'));
	}


	public function createService0468(): PHPStan\Type\Php\CurlGetinfoFunctionDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\CurlGetinfoFunctionDynamicReturnTypeExtension($this->getService('reflectionProvider'));
	}


	public function createService0469(): PHPStan\Type\Php\PropertyExistsTypeSpecifyingExtension
	{
		return new PHPStan\Type\Php\PropertyExistsTypeSpecifyingExtension($this->getService('0133'));
	}


	public function createService0470(): PHPStan\Type\Php\ArrayMapParameterClosureTypeExtension
	{
		return new PHPStan\Type\Php\ArrayMapParameterClosureTypeExtension;
	}


	public function createService0471(): PHPStan\Type\Php\MbConvertEncodingFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\MbConvertEncodingFunctionReturnTypeExtension;
	}


	public function createService0472(): PHPStan\Type\Php\BackedEnumFromMethodDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\BackedEnumFromMethodDynamicReturnTypeExtension;
	}


	public function createService0473(): PHPStan\Type\Php\PDOConnectReturnTypeExtension
	{
		return new PHPStan\Type\Php\PDOConnectReturnTypeExtension($this->getService('03'));
	}


	public function createService0474(): PHPStan\Type\Php\GetDefinedVarsFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\GetDefinedVarsFunctionReturnTypeExtension;
	}


	public function createService0475(): PHPStan\Type\Php\SimpleXMLElementXpathMethodReturnTypeExtension
	{
		return new PHPStan\Type\Php\SimpleXMLElementXpathMethodReturnTypeExtension;
	}


	public function createService0476(): PHPStan\Type\Php\PregMatchParameterOutTypeExtension
	{
		return new PHPStan\Type\Php\PregMatchParameterOutTypeExtension($this->getService('0386'));
	}


	public function createService0477(): PHPStan\Type\Php\DioStatDynamicFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\DioStatDynamicFunctionReturnTypeExtension;
	}


	public function createService0478(): PHPStan\Type\Php\ArrayShiftFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayShiftFunctionReturnTypeExtension;
	}


	public function createService0479(): PHPStan\Type\Php\ReplaceFunctionsDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\ReplaceFunctionsDynamicReturnTypeExtension;
	}


	public function createService0480(): PHPStan\Type\Php\GetDebugTypeFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\GetDebugTypeFunctionReturnTypeExtension;
	}


	public function createService0481(): PHPStan\Type\Php\IdateFunctionReturnTypeHelper
	{
		return new PHPStan\Type\Php\IdateFunctionReturnTypeHelper;
	}


	public function createService0482(): PHPStan\Type\Php\MicrotimeFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\MicrotimeFunctionReturnTypeExtension;
	}


	public function createService0483(): PHPStan\Type\Php\DateFormatFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\DateFormatFunctionReturnTypeExtension($this->getService('0456'));
	}


	public function createService0484(): PHPStan\Type\Php\GmpUnaryOperatorTypeSpecifyingExtension
	{
		return new PHPStan\Type\Php\GmpUnaryOperatorTypeSpecifyingExtension;
	}


	public function createService0485(): PHPStan\Type\Php\ArrayFindKeyFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayFindKeyFunctionReturnTypeExtension;
	}


	public function createService0486(): PHPStan\Type\Php\CountFunctionTypeSpecifyingExtension
	{
		return new PHPStan\Type\Php\CountFunctionTypeSpecifyingExtension;
	}


	public function createService0487(): PHPStan\Type\Php\ArrayFilterParameterClosureTypeExtension
	{
		return new PHPStan\Type\Php\ArrayFilterParameterClosureTypeExtension;
	}


	public function createService0488(): PHPStan\Type\Php\FilterVarArrayDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\FilterVarArrayDynamicReturnTypeExtension(
			$this->getService('0545'),
			$this->getService('reflectionProvider')
		);
	}


	public function createService0489(): PHPStan\Type\Php\ArrayKeysFunctionDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayKeysFunctionDynamicReturnTypeExtension;
	}


	public function createService0490(): PHPStan\Type\Php\StatDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\StatDynamicReturnTypeExtension;
	}


	public function createService0491(): PHPStan\Type\Php\StrRepeatFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\StrRepeatFunctionReturnTypeExtension;
	}


	public function createService0492(): PHPStan\Type\Php\ArrayWalkParameterClosureTypeExtension
	{
		return new PHPStan\Type\Php\ArrayWalkParameterClosureTypeExtension;
	}


	public function createService0493(): PHPStan\Type\Php\ArrayFillFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayFillFunctionReturnTypeExtension;
	}


	public function createService0494(): PHPStan\Type\Php\PgResultStatusDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\PgResultStatusDynamicReturnTypeExtension;
	}


	public function createService0495(): PHPStan\Type\Php\ArrayFindFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayFindFunctionReturnTypeExtension($this->getService('0418'));
	}


	public function createService0496(): PHPStan\Type\Php\ArrayObjectConstructorReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayObjectConstructorReturnTypeExtension;
	}


	public function createService0497(): PHPStan\Type\Php\SimpleXMLElementConstructorThrowTypeExtension
	{
		return new PHPStan\Type\Php\SimpleXMLElementConstructorThrowTypeExtension;
	}


	public function createService0498(): PHPStan\Type\Php\GettimeofdayDynamicFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\GettimeofdayDynamicFunctionReturnTypeExtension;
	}


	public function createService0499(): PHPStan\Type\Php\DateFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\DateFunctionReturnTypeExtension($this->getService('0456'));
	}


	public function createService0500(): PHPStan\Type\Php\DateIntervalCreateFromDateStringThrowTypeExtension
	{
		return new PHPStan\Type\Php\DateIntervalCreateFromDateStringThrowTypeExtension;
	}


	public function createService0501(): PHPStan\Type\Php\UnserializeFunctionThrowTypeExtension
	{
		return new PHPStan\Type\Php\UnserializeFunctionThrowTypeExtension;
	}


	public function createService0502(): PHPStan\Type\Php\OutputBufferingDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\OutputBufferingDynamicReturnTypeExtension;
	}


	public function createService0503(): PHPStan\Type\Php\ClampFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\ClampFunctionReturnTypeExtension;
	}


	public function createService0504(): PHPStan\Type\Php\PgLastNoticeDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\PgLastNoticeDynamicReturnTypeExtension;
	}


	public function createService0505(): PHPStan\Type\Php\GetParentClassDynamicFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\GetParentClassDynamicFunctionReturnTypeExtension($this->getService('reflectionProvider'));
	}


	public function createService0506(): PHPStan\Type\Php\PrintfFunctionThrowTypeExtension
	{
		return new PHPStan\Type\Php\PrintfFunctionThrowTypeExtension($this->getService('0352'));
	}


	public function createService0507(): PHPStan\Type\Php\DatePeriodConstructorReturnTypeExtension
	{
		return new PHPStan\Type\Php\DatePeriodConstructorReturnTypeExtension;
	}


	public function createService0508(): PHPStan\Type\Php\DateTimeCreateDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\DateTimeCreateDynamicReturnTypeExtension;
	}


	public function createService0509(): PHPStan\Type\Php\ArrayCombineFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayCombineFunctionReturnTypeExtension($this->getService('0534'));
	}


	public function createService0510(): PHPStan\Type\Php\GetClassDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\GetClassDynamicReturnTypeExtension;
	}


	public function createService0511(): PHPStan\Type\Php\ArrayReduceFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayReduceFunctionReturnTypeExtension;
	}


	public function createService0512(): PHPStan\Type\Php\MbSubstituteCharacterDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\MbSubstituteCharacterDynamicReturnTypeExtension;
	}


	public function createService0513(): PHPStan\Type\Php\ArrayFlipFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayFlipFunctionReturnTypeExtension;
	}


	public function createService0514(): PHPStan\Type\Php\ArrayKeyExistsFunctionTypeSpecifyingExtension
	{
		return new PHPStan\Type\Php\ArrayKeyExistsFunctionTypeSpecifyingExtension;
	}


	public function createService0515(): PHPStan\Type\Php\RoundFunctionThrowTypeExtension
	{
		return new PHPStan\Type\Php\RoundFunctionThrowTypeExtension;
	}


	public function createService0516(): PHPStan\Type\Php\ReflectionFunctionConstructorThrowTypeExtension
	{
		return new PHPStan\Type\Php\ReflectionFunctionConstructorThrowTypeExtension($this->getService('reflectionProvider'));
	}


	public function createService0517(): PHPStan\Type\Php\ArrayChunkFunctionThrowTypeExtension
	{
		return new PHPStan\Type\Php\ArrayChunkFunctionThrowTypeExtension;
	}


	public function createService0518(): PHPStan\Type\Php\SetTypeFunctionTypeSpecifyingExtension
	{
		return new PHPStan\Type\Php\SetTypeFunctionTypeSpecifyingExtension;
	}


	public function createService0519(): PHPStan\Type\Php\DateIntervalFormatDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\DateIntervalFormatDynamicReturnTypeExtension($this->getService('0371'));
	}


	public function createService0520(): PHPStan\Type\Php\GmpOperatorTypeSpecifyingExtension
	{
		return new PHPStan\Type\Php\GmpOperatorTypeSpecifyingExtension;
	}


	public function createService0521(): PHPStan\Type\Php\ArrayChunkFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayChunkFunctionReturnTypeExtension;
	}


	public function createService0522(): PHPStan\Type\Php\HrtimeFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\HrtimeFunctionReturnTypeExtension;
	}


	public function createService0523(): PHPStan\Type\Php\ReflectionPropertyConstructorThrowTypeExtension
	{
		return new PHPStan\Type\Php\ReflectionPropertyConstructorThrowTypeExtension($this->getService('reflectionProvider'));
	}


	public function createService0524(): PHPStan\Type\Php\MbFunctionsReturnTypeExtension
	{
		return new PHPStan\Type\Php\MbFunctionsReturnTypeExtension;
	}


	public function createService0525(): PHPStan\Type\Php\StrlenFunctionTypeSpecifyingExtension
	{
		return new PHPStan\Type\Php\StrlenFunctionTypeSpecifyingExtension;
	}


	public function createService0526(): PHPStan\Type\Php\StrIncrementDecrementFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\StrIncrementDecrementFunctionReturnTypeExtension;
	}


	public function createService0527(): PHPStan\Type\Php\StrContainingTypeSpecifyingExtension
	{
		return new PHPStan\Type\Php\StrContainingTypeSpecifyingExtension;
	}


	public function createService0528(): PHPStan\Type\Php\FunctionExistsFunctionTypeSpecifyingExtension
	{
		return new PHPStan\Type\Php\FunctionExistsFunctionTypeSpecifyingExtension;
	}


	public function createService0529(): PHPStan\Type\Php\ImplodeFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\ImplodeFunctionReturnTypeExtension;
	}


	public function createService0530(): PHPStan\Type\Php\ArrayRandFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayRandFunctionReturnTypeExtension;
	}


	public function createService0531(): PHPStan\Type\Php\HighlightStringDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\HighlightStringDynamicReturnTypeExtension;
	}


	public function createService0532(): PHPStan\Type\Php\DateTimeZoneConstructorThrowTypeExtension
	{
		return new PHPStan\Type\Php\DateTimeZoneConstructorThrowTypeExtension;
	}


	public function createService0533(): PHPStan\Type\Php\PathinfoFunctionDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\PathinfoFunctionDynamicReturnTypeExtension($this->getService('reflectionProvider'));
	}


	public function createService0534(): PHPStan\Type\Php\ArrayCombineHelper
	{
		return new PHPStan\Type\Php\ArrayCombineHelper;
	}


	public function createService0535(): PHPStan\Type\Php\CountCharsFunctionDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\CountCharsFunctionDynamicReturnTypeExtension;
	}


	public function createService0536(): PHPStan\Type\Php\ArrayMapFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayMapFunctionReturnTypeExtension;
	}


	public function createService0537(): PHPStan\Type\Php\ArrayPadDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayPadDynamicReturnTypeExtension;
	}


	public function createService0538(): PHPStan\Type\Php\CtypeDigitFunctionTypeSpecifyingExtension
	{
		return new PHPStan\Type\Php\CtypeDigitFunctionTypeSpecifyingExtension;
	}


	public function createService0539(): PHPStan\Type\Php\RoundFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\RoundFunctionReturnTypeExtension;
	}


	public function createService0540(): PHPStan\Type\Php\TriggerErrorFunctionThrowTypeExtension
	{
		return new PHPStan\Type\Php\TriggerErrorFunctionThrowTypeExtension($this->getParameter('exceptions')['implicitThrows']);
	}


	public function createService0541(): PHPStan\Type\Php\BcMathNumberUnaryOperatorTypeSpecifyingExtension
	{
		return new PHPStan\Type\Php\BcMathNumberUnaryOperatorTypeSpecifyingExtension($this->getService('03'));
	}


	public function createService0542(): PHPStan\Type\Php\RangeFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\RangeFunctionReturnTypeExtension;
	}


	public function createService0543(): PHPStan\Type\Php\ArrayValuesFunctionDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayValuesFunctionDynamicReturnTypeExtension;
	}


	public function createService0544(): PHPStan\Type\Php\OpensslCipherFunctionsReturnTypeExtension
	{
		return new PHPStan\Type\Php\OpensslCipherFunctionsReturnTypeExtension($this->getService('0375'));
	}


	public function createService0545(): PHPStan\Type\Php\FilterFunctionReturnTypeHelper
	{
		return new PHPStan\Type\Php\FilterFunctionReturnTypeHelper($this->getService('reflectionProvider'));
	}


	public function createService0546(): PHPStan\Type\Php\TrimFunctionDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\TrimFunctionDynamicReturnTypeExtension;
	}


	public function createService0547(): PHPStan\Type\Php\StrTokFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\StrTokFunctionReturnTypeExtension;
	}


	public function createService0548(): PHPStan\Type\Php\ConstantFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\ConstantFunctionReturnTypeExtension($this->getService('0370'));
	}


	public function createService0549(): PHPStan\Type\Php\ArraySumFunctionDynamicReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArraySumFunctionDynamicReturnTypeExtension;
	}


	public function createService0550(): PHPStan\Type\Php\StrCaseFunctionsReturnTypeExtension
	{
		return new PHPStan\Type\Php\StrCaseFunctionsReturnTypeExtension;
	}


	public function createService0551(): PHPStan\Type\Php\ArrayColumnFunctionReturnTypeExtension
	{
		return new PHPStan\Type\Php\ArrayColumnFunctionReturnTypeExtension($this->getService('0376'));
	}


	public function createService0552(): PHPStan\Type\UnaryOperatorTypeSpecifyingExtensionRegistry
	{
		return new PHPStan\Type\UnaryOperatorTypeSpecifyingExtensionRegistry($this->getService('phpstan.extensionsCollection.PHPStan.Type.UnaryOperatorTypeSpecifyingExtension'));
	}


	public function createService0553(): PHPStan\Type\DynamicReturnTypeExtensionRegistry
	{
		return new PHPStan\Type\DynamicReturnTypeExtensionRegistry(
			$this->getService('reflectionProvider'),
			$this->getService('phpstan.extensionsCollection.PHPStan.Type.DynamicMethodReturnTypeExtension'),
			$this->getService('phpstan.extensionsCollection.PHPStan.Type.DynamicStaticMethodReturnTypeExtension'),
			$this->getService('phpstan.extensionsCollection.PHPStan.Type.DynamicFunctionReturnTypeExtension')
		);
	}


	public function createService0554(): PHPStan\Type\OperatorTypeSpecifyingExtensionRegistry
	{
		return new PHPStan\Type\OperatorTypeSpecifyingExtensionRegistry($this->getService('phpstan.extensionsCollection.PHPStan.Type.OperatorTypeSpecifyingExtension'));
	}


	public function createService0555(): PHPStan\Type\Constant\OversizedArrayBuilder
	{
		return new PHPStan\Type\Constant\OversizedArrayBuilder;
	}


	public function createService0556(): PHPStan\Type\FileTypeMapper
	{
		return new PHPStan\Type\FileTypeMapper(
			$this->getService('054'),
			$this->getService('defaultAnalysisParser'),
			$this->getService('019'),
			$this->getService('021'),
			$this->getService('030'),
			$this->getService('09'),
			$this->getService('029'),
			$this->getService('07'),
			$this->getParameter('cache')['resolvedPhpDocBlockCacheCountMax'],
			$this->getParameter('cache')['nameScopeMapMemoryCacheCountMax']
		);
	}


	public function createService0557(): PHPStan\Type\BitwiseFlagHelper
	{
		return new PHPStan\Type\BitwiseFlagHelper($this->getService('reflectionProvider'));
	}


	public function createService0558(): PHPStan\Type\ArrayUnpackingHelper
	{
		return new PHPStan\Type\ArrayUnpackingHelper($this->getService('03'));
	}


	public function createService0559(): PHPStan\Type\LazyTypeAliasResolverProvider
	{
		return new PHPStan\Type\LazyTypeAliasResolverProvider($this->getService('0333'));
	}


	public function createService0560(): PHPStan\Type\PHPStan\ClassNameUsageLocationCreateIdentifierDynamicReturnTypeExtension
	{
		return new PHPStan\Type\PHPStan\ClassNameUsageLocationCreateIdentifierDynamicReturnTypeExtension;
	}


	public function createService0561(): PHPStan\Type\ClosureTypeFactory
	{
		return new PHPStan\Type\ClosureTypeFactory(
			$this->getService('072'),
			$this->getService('0896'),
			$this->getService('betterReflectionReflector'),
			$this->getService('054'),
			$this->getService('currentPhpVersionPhpParser')
		);
	}


	public function createService0562(): PHPStan\Type\UsefulTypeAliasResolver
	{
		return new PHPStan\Type\UsefulTypeAliasResolver(
			$this->getParameter('typeAliases'),
			$this->getService('025'),
			$this->getService('026'),
			$this->getService('reflectionProvider'),
			$this->getParameter('cache')['resolvedLocalTypeAliasesCountMax']
		);
	}


	public function createService0563(): PHPStan\File\FileExcluderRawFactory
	{
		return new class ($this) implements PHPStan\File\FileExcluderRawFactory {
			private $container;


			public function __construct(Container_93320a03c5 $container)
			{
				$this->container = $container;
			}


			public function create(array $analyseExcludes): PHPStan\File\FileExcluder
			{
				return new PHPStan\File\FileExcluder($this->container->getService('09'), $analyseExcludes);
			}
		};
	}


	public function createService0564(): PHPStan\Reflection\Php\PhpMethodReflectionFactory
	{
		return new class ($this) implements PHPStan\Reflection\Php\PhpMethodReflectionFactory {
			private $container;


			public function __construct(Container_93320a03c5 $container)
			{
				$this->container = $container;
			}


			public function create(
				PHPStan\Reflection\ClassReflection $declaringClass,
				?PHPStan\Reflection\ClassReflection $declaringTrait,
				PHPStan\BetterReflection\Reflection\Adapter\ReflectionMethod $reflection,
				PHPStan\Type\Generic\TemplateTypeMap $templateTypeMap,
				array $phpDocParameterTypes,
				?PHPStan\Type\Type $phpDocReturnType,
				?PHPStan\Type\Type $phpDocThrowType,
				?PHPStan\PhpDoc\ResolvedPhpDocBlock $resolvedPhpDocBlock,
				?string $deprecatedDescription,
				bool $isDeprecated,
				bool $isInternal,
				bool $isFinal,
				?bool $isPure,
				PHPStan\Reflection\Assertions $asserts,
				?PHPStan\Type\Type $selfOutType,
				?string $phpDocComment,
				array $phpDocParameterOutTypes,
				array $immediatelyInvokedCallableParameters,
				array $phpDocClosureThisTypeParameters,
				bool $acceptsNamedArguments,
				array $attributes,
				array $pureUnlessCallableIsImpureParameters,
				array $pureUnlessParameterPassedParameters
			): PHPStan\Reflection\Php\PhpMethodReflection {
				return new PHPStan\Reflection\Php\PhpMethodReflection(
					$this->container->getService('072'),
					$declaringClass,
					$declaringTrait,
					$reflection,
					$this->container->getService('reflectionProvider'),
					$this->container->getService('052'),
					$this->container->getService('053'),
					$templateTypeMap,
					$phpDocParameterTypes,
					$phpDocReturnType,
					$phpDocThrowType,
					$resolvedPhpDocBlock,
					$deprecatedDescription,
					$isDeprecated,
					$isInternal,
					$isFinal,
					$isPure,
					$asserts,
					$acceptsNamedArguments,
					$selfOutType,
					$phpDocComment,
					$phpDocParameterOutTypes,
					$immediatelyInvokedCallableParameters,
					$phpDocClosureThisTypeParameters,
					$attributes,
					$pureUnlessCallableIsImpureParameters,
					$pureUnlessParameterPassedParameters
				);
			}
		};
	}


	public function createService0565(): PHPStan\Reflection\FunctionReflectionFactory
	{
		return new class ($this) implements PHPStan\Reflection\FunctionReflectionFactory {
			private $container;


			public function __construct(Container_93320a03c5 $container)
			{
				$this->container = $container;
			}


			public function create(
				PHPStan\BetterReflection\Reflection\Adapter\ReflectionFunction $reflection,
				PHPStan\Type\Generic\TemplateTypeMap $templateTypeMap,
				array $phpDocParameterTypes,
				?PHPStan\Type\Type $phpDocReturnType,
				?PHPStan\Type\Type $phpDocThrowType,
				?string $deprecatedDescription,
				bool $isDeprecated,
				bool $isInternal,
				?string $filename,
				?bool $isPure,
				PHPStan\Reflection\Assertions $asserts,
				bool $acceptsNamedArguments,
				?string $phpDocComment,
				array $phpDocParameterOutTypes,
				array $phpDocParameterImmediatelyInvokedCallable,
				array $phpDocParameterClosureThisTypes,
				array $attributes,
				array $phpDocParameterPureUnlessCallableIsImpure,
				array $phpDocParameterPureUnlessParameterPassed
			): PHPStan\Reflection\Php\PhpFunctionReflection {
				return new PHPStan\Reflection\Php\PhpFunctionReflection(
					$this->container->getService('072'),
					$reflection,
					$this->container->getService('052'),
					$this->container->getService('053'),
					$templateTypeMap,
					$phpDocParameterTypes,
					$phpDocReturnType,
					$phpDocThrowType,
					$deprecatedDescription,
					$isDeprecated,
					$isInternal,
					$filename,
					$isPure,
					$asserts,
					$acceptsNamedArguments,
					$phpDocComment,
					$phpDocParameterOutTypes,
					$phpDocParameterImmediatelyInvokedCallable,
					$phpDocParameterClosureThisTypes,
					$attributes,
					$phpDocParameterPureUnlessCallableIsImpure,
					$phpDocParameterPureUnlessParameterPassed
				);
			}
		};
	}


	public function createService0566(): PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedSingleFileSourceLocatorFactory
	{
		return new class ($this) implements PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedSingleFileSourceLocatorFactory {
			private $container;


			public function __construct(Container_93320a03c5 $container)
			{
				$this->container = $container;
			}


			public function create(string $fileName): PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedSingleFileSourceLocator
			{
				return new PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedSingleFileSourceLocator(
					$this->container->getService('056'),
					$this->container->getService('029'),
					$this->container->getService('03'),
					$fileName
				);
			}
		};
	}


	public function createService0567(): PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedPsrAutoloaderLocatorFactory
	{
		return new class ($this) implements PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedPsrAutoloaderLocatorFactory {
			private $container;


			public function __construct(Container_93320a03c5 $container)
			{
				$this->container = $container;
			}


			public function create(PHPStan\BetterReflection\SourceLocator\Type\Composer\Psr\PsrAutoloaderMapping $mapping): PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedPsrAutoloaderLocator
			{
				return new PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedPsrAutoloaderLocator($mapping, $this->container->getService('063'));
			}
		};
	}


	public function createService0568(): PHPStan\Reflection\ClassReflectionFactory
	{
		return new class ($this) implements PHPStan\Reflection\ClassReflectionFactory {
			private $container;


			public function __construct(Container_93320a03c5 $container)
			{
				$this->container = $container;
			}


			public function create(
				string $displayName,
				ReflectionClass $reflection,
				?string $anonymousFilename,
				?PHPStan\Type\Generic\TemplateTypeMap $resolvedTemplateTypeMap,
				?Closure $stubPhpDocBlockCallback,
				?string $extraCacheKey = null,
				?PHPStan\Type\Generic\TemplateTypeVarianceMap $resolvedCallSiteVarianceMap = null,
				?bool $finalByKeywordOverride = null
			): PHPStan\Reflection\ClassReflection {
				return new PHPStan\Reflection\ClassReflection(
					$this->container->getService('0568'),
					$this->container->getService('reflectionProvider'),
					$this->container->getService('072'),
					$this->container->getService('0556'),
					$this->container->getService('stubPhpDocProvider'),
					$this->container->getService('018'),
					$this->container->getService('03'),
					$this->container->getService('075'),
					$this->container->getService('079'),
					$this->container->getService('052'),
					$this->container->getService('0331'),
					$displayName,
					$reflection,
					$anonymousFilename,
					$resolvedTemplateTypeMap,
					$stubPhpDocBlockCallback,
					$extraCacheKey,
					$resolvedCallSiteVarianceMap,
					$finalByKeywordOverride
				);
			}
		};
	}


	public function createService0569(): PHPStan\Analyser\ExpressionResultFactory
	{
		return new class ($this) implements PHPStan\Analyser\ExpressionResultFactory {
			private $container;


			public function __construct(Container_93320a03c5 $container)
			{
				$this->container = $container;
			}


			public function create(
				PHPStan\Analyser\MutatingScope $scope,
				PHPStan\Analyser\MutatingScope $beforeScope,
				PhpParser\Node\Expr $expr,
				bool $hasYield,
				bool $isAlwaysTerminating,
				array $throwPoints,
				array $impurePoints,
				?callable $typeCallback,
				callable $specifyTypesCallback,
				bool $containsNullsafe = false,
				?PHPStan\Analyser\IssetabilityDescriptor $issetabilityDescriptor = null,
				?PHPStan\Analyser\ExpressionResult $truthyScopeOverrideResult = null,
				?PHPStan\Analyser\ExpressionResult $falseyScopeOverrideResult = null,
				?callable $createTypesCallback = null,
				?PHPStan\Type\Type $type = null,
				?PHPStan\Type\Type $nativeType = null,
				?PHPStan\Analyser\ArgsResult $argsResult = null,
				?PHPStan\Analyser\VariableFlow $variableFlow = null,
				?PHPStan\Dependency\Dependencies $dependencies = null
			): PHPStan\Analyser\ExpressionResult {
				return new PHPStan\Analyser\ExpressionResult(
					$this->container->getService('phpstan.extensionsCollection.PHPStan.Type.ExpressionTypeResolverExtension'),
					$this->container->getService('0270'),
					$scope,
					$beforeScope,
					$expr,
					$hasYield,
					$isAlwaysTerminating,
					$throwPoints,
					$impurePoints,
					$typeCallback,
					$specifyTypesCallback,
					$containsNullsafe,
					$issetabilityDescriptor,
					$truthyScopeOverrideResult,
					$falseyScopeOverrideResult,
					$createTypesCallback,
					$type,
					$nativeType,
					$argsResult,
					$variableFlow,
					dependencies: $dependencies
				);
			}
		};
	}


	public function createService0570(): PHPStan\Analyser\InternalScopeFactoryFactory
	{
		return new class ($this) implements PHPStan\Analyser\InternalScopeFactoryFactory {
			private $container;


			public function __construct(Container_93320a03c5 $container)
			{
				$this->container = $container;
			}


			public function create(?callable $nodeCallback): PHPStan\Analyser\InternalScopeFactory
			{
				return new PHPStan\Analyser\LazyInternalScopeFactory($this->container->getService('0333'), $nodeCallback);
			}
		};
	}


	public function createService0571(): PHPStan\Analyser\ResultCache\ResultCacheManagerFactory
	{
		return new class ($this) implements PHPStan\Analyser\ResultCache\ResultCacheManagerFactory {
			private $container;


			public function __construct(Container_93320a03c5 $container)
			{
				$this->container = $container;
			}


			public function create(array $fileReplacements): PHPStan\Analyser\ResultCache\ResultCacheManager
			{
				return new PHPStan\Analyser\ResultCache\ResultCacheManager(
					$this->container->getService('phpstan.extensionsCollection.PHPStan.Analyser.ResultCache.ResultCacheMetaExtension'),
					$this->container->getService('034'),
					$this->container->getService('fileFinderScan'),
					$this->container->getService('017'),
					$this->container->getService('09'),
					$this->container->getService('032'),
					$this->container->getService('066'),
					$this->container->getParameter('resultCachePath'),
					$this->container->getParameter('analysedPaths'),
					$this->container->getParameter('analysedPathsFromConfig'),
					$this->container->getParameter('composerAutoloaderProjectPaths'),
					$this->container->getParameter('usedLevel'),
					$this->container->getParameter('cliAutoloadFile'),
					$this->container->getParameter('bootstrapFiles'),
					$this->container->getParameter('scanFiles'),
					$this->container->getParameter('scanDirectories'),
					$this->container->getParameter('stubFiles'),
					$fileReplacements,
					$this->container->getParameter('resultCacheChecksProjectExtensionFilesDependencies'),
					$this->container->getParameter('parametersNotInvalidatingCache'),
					$this->container->getParameter('resultCacheSkipIfOlderThanDays'),
					$this->container->getParameter('rootDir'),
					$this->container->getService('03'),
					$this->container->getService('04'),
					$this->container->getService('08'),
					$this->container->getService('0320')
				);
			}
		};
	}


	public function createService0572(): PHPStan\Rules\Namespaces\ExistingNamesInUseRule
	{
		return new PHPStan\Rules\Namespaces\ExistingNamesInUseRule(
			$this->getService('reflectionProvider'),
			$this->getService('0124'),
			$this->getParameter('checkFunctionNameCase'),
			$this->getParameter('tips')['discoveringSymbols']
		);
	}


	public function createService0573(): PHPStan\Rules\Namespaces\ExistingNamesInGroupUseRule
	{
		return new PHPStan\Rules\Namespaces\ExistingNamesInGroupUseRule(
			$this->getService('reflectionProvider'),
			$this->getService('0124'),
			$this->getParameter('checkFunctionNameCase'),
			$this->getParameter('tips')['discoveringSymbols']
		);
	}


	public function createService0574(): PHPStan\Rules\Generators\YieldTypeRule
	{
		return new PHPStan\Rules\Generators\YieldTypeRule($this->getService('0118'));
	}


	public function createService0575(): PHPStan\Rules\Generators\YieldFromTypeRule
	{
		return new PHPStan\Rules\Generators\YieldFromTypeRule($this->getService('0118'), $this->getParameter('reportMaybes'));
	}


	public function createService0576(): PHPStan\Rules\Generators\YieldInGeneratorRule
	{
		return new PHPStan\Rules\Generators\YieldInGeneratorRule($this->getParameter('reportMaybes'));
	}


	public function createService0577(): PHPStan\Rules\PhpDoc\IncompatiblePropertyHookPhpDocTypeRule
	{
		return new PHPStan\Rules\PhpDoc\IncompatiblePropertyHookPhpDocTypeRule($this->getService('0556'), $this->getService('0112'));
	}


	public function createService0578(): PHPStan\Rules\PhpDoc\IncompatibleSelfOutTypeRule
	{
		return new PHPStan\Rules\PhpDoc\IncompatibleSelfOutTypeRule($this->getService('0114'), $this->getService('0177'));
	}


	public function createService0579(): PHPStan\Rules\PhpDoc\SealedDefinitionTraitRule
	{
		return new PHPStan\Rules\PhpDoc\SealedDefinitionTraitRule($this->getService('reflectionProvider'));
	}


	public function createService0580(): PHPStan\Rules\PhpDoc\IncompatibleClassConstantPhpDocTypeRule
	{
		return new PHPStan\Rules\PhpDoc\IncompatibleClassConstantPhpDocTypeRule($this->getService('0177'), $this->getService('0114'));
	}


	public function createService0581(): PHPStan\Rules\PhpDoc\FunctionConditionalReturnTypeRule
	{
		return new PHPStan\Rules\PhpDoc\FunctionConditionalReturnTypeRule($this->getService('0115'));
	}


	public function createService0582(): PHPStan\Rules\PhpDoc\InvalidPHPStanDocTagRule
	{
		return new PHPStan\Rules\PhpDoc\InvalidPHPStanDocTagRule($this->getService('0890'), $this->getService('0893'));
	}


	public function createService0583(): PHPStan\Rules\PhpDoc\InvalidPhpDocVarTagTypeRule
	{
		return new PHPStan\Rules\PhpDoc\InvalidPhpDocVarTagTypeRule(
			$this->getService('0556'),
			$this->getService('reflectionProvider'),
			$this->getService('0124'),
			$this->getService('0177'),
			$this->getService('0153'),
			$this->getService('0114'),
			$this->getParameter('checkClassCaseSensitivity'),
			$this->getParameter('checkMissingVarTagTypehint'),
			$this->getParameter('tips')['discoveringSymbols']
		);
	}


	public function createService0584(): PHPStan\Rules\PhpDoc\VarTagChangedExpressionTypeRule
	{
		return new PHPStan\Rules\PhpDoc\VarTagChangedExpressionTypeRule($this->getService('0111'));
	}


	public function createService0585(): PHPStan\Rules\PhpDoc\SealedDefinitionClassRule
	{
		return new PHPStan\Rules\PhpDoc\SealedDefinitionClassRule(
			$this->getService('reflectionProvider'),
			$this->getService('0124'),
			$this->getParameter('checkClassCaseSensitivity'),
			$this->getParameter('tips')['discoveringSymbols']
		);
	}


	public function createService0586(): PHPStan\Rules\PhpDoc\IncompatiblePropertyPhpDocTypeRule
	{
		return new PHPStan\Rules\PhpDoc\IncompatiblePropertyPhpDocTypeRule(
			$this->getService('0177'),
			$this->getService('0114'),
			$this->getService('0117')
		);
	}


	public function createService0587(): PHPStan\Rules\PhpDoc\IncompatiblePhpDocTypeRule
	{
		return new PHPStan\Rules\PhpDoc\IncompatiblePhpDocTypeRule($this->getService('0556'), $this->getService('0112'));
	}


	public function createService0588(): PHPStan\Rules\PhpDoc\FunctionAssertRule
	{
		return new PHPStan\Rules\PhpDoc\FunctionAssertRule($this->getService('0116'));
	}


	public function createService0589(): PHPStan\Rules\PhpDoc\InvalidThrowsPhpDocValueRule
	{
		return new PHPStan\Rules\PhpDoc\InvalidThrowsPhpDocValueRule($this->getService('0556'));
	}


	public function createService0590(): PHPStan\Rules\PhpDoc\RequireImplementsDefinitionClassRule
	{
		return new PHPStan\Rules\PhpDoc\RequireImplementsDefinitionClassRule;
	}


	public function createService0591(): PHPStan\Rules\PhpDoc\RequireExtendsDefinitionClassRule
	{
		return new PHPStan\Rules\PhpDoc\RequireExtendsDefinitionClassRule($this->getService('0113'));
	}


	public function createService0592(): PHPStan\Rules\PhpDoc\MethodConditionalReturnTypeRule
	{
		return new PHPStan\Rules\PhpDoc\MethodConditionalReturnTypeRule($this->getService('0115'));
	}


	public function createService0593(): PHPStan\Rules\PhpDoc\RequireImplementsDefinitionTraitRule
	{
		return new PHPStan\Rules\PhpDoc\RequireImplementsDefinitionTraitRule(
			$this->getService('reflectionProvider'),
			$this->getService('0124'),
			$this->getParameter('checkClassCaseSensitivity'),
			$this->getParameter('tips')['discoveringSymbols']
		);
	}


	public function createService0594(): PHPStan\Rules\PhpDoc\IncompatibleParamImmediatelyInvokedCallableRule
	{
		return new PHPStan\Rules\PhpDoc\IncompatibleParamImmediatelyInvokedCallableRule($this->getService('0556'));
	}


	public function createService0595(): PHPStan\Rules\PhpDoc\WrongVariableNameInVarTagRule
	{
		return new PHPStan\Rules\PhpDoc\WrongVariableNameInVarTagRule($this->getService('0556'), $this->getService('0111'));
	}


	public function createService0596(): PHPStan\Rules\PhpDoc\RequireExtendsDefinitionTraitRule
	{
		return new PHPStan\Rules\PhpDoc\RequireExtendsDefinitionTraitRule(
			$this->getService('reflectionProvider'),
			$this->getService('0113')
		);
	}


	public function createService0597(): PHPStan\Rules\PhpDoc\InvalidPhpDocTagValueRule
	{
		return new PHPStan\Rules\PhpDoc\InvalidPhpDocTagValueRule($this->getService('0890'), $this->getService('0893'));
	}


	public function createService0598(): PHPStan\Rules\PhpDoc\MethodAssertRule
	{
		return new PHPStan\Rules\PhpDoc\MethodAssertRule($this->getService('0116'));
	}


	public function createService0599(): PHPStan\Rules\DateTimeInstantiationRule
	{
		return new PHPStan\Rules\DateTimeInstantiationRule;
	}


	public function createService0600(): PHPStan\Rules\Whitespace\FileWhitespaceRule
	{
		return new PHPStan\Rules\Whitespace\FileWhitespaceRule;
	}


	public function createService0601(): PHPStan\Rules\Missing\MissingReturnRule
	{
		return new PHPStan\Rules\Missing\MissingReturnRule(
			$this->getParameter('checkExplicitMixedMissingReturn'),
			$this->getParameter('checkPhpDocMissingReturn')
		);
	}


	public function createService0602(): PHPStan\Rules\Exceptions\CatchWithUnthrownExceptionRule
	{
		return new PHPStan\Rules\Exceptions\CatchWithUnthrownExceptionRule(
			$this->getService('exceptionTypeResolver'),
			$this->getParameter('exceptions')['reportUncheckedExceptionDeadCatch']
		);
	}


	public function createService0603(): PHPStan\Rules\Exceptions\ThrowsVoidFunctionWithExplicitThrowPointRule
	{
		return new PHPStan\Rules\Exceptions\ThrowsVoidFunctionWithExplicitThrowPointRule(
			$this->getService('exceptionTypeResolver'),
			$this->getParameter('exceptions')['check']['missingCheckedExceptionInThrows']
		);
	}


	public function createService0604(): PHPStan\Rules\Exceptions\ThrowExprTypeRule
	{
		return new PHPStan\Rules\Exceptions\ThrowExprTypeRule($this->getService('0118'));
	}


	public function createService0605(): PHPStan\Rules\Exceptions\ThrowExpressionRule
	{
		return new PHPStan\Rules\Exceptions\ThrowExpressionRule;
	}


	public function createService0606(): PHPStan\Rules\Exceptions\CaughtExceptionExistenceRule
	{
		return new PHPStan\Rules\Exceptions\CaughtExceptionExistenceRule(
			$this->getService('reflectionProvider'),
			$this->getService('0124'),
			$this->getParameter('checkClassCaseSensitivity'),
			$this->getParameter('tips')['discoveringSymbols']
		);
	}


	public function createService0607(): PHPStan\Rules\Exceptions\NoncapturingCatchRule
	{
		return new PHPStan\Rules\Exceptions\NoncapturingCatchRule;
	}


	public function createService0608(): PHPStan\Rules\Exceptions\ThrowsVoidPropertyHookWithExplicitThrowPointRule
	{
		return new PHPStan\Rules\Exceptions\ThrowsVoidPropertyHookWithExplicitThrowPointRule(
			$this->getService('exceptionTypeResolver'),
			$this->getParameter('exceptions')['check']['missingCheckedExceptionInThrows']
		);
	}


	public function createService0609(): PHPStan\Rules\Exceptions\ThrowsVoidMethodWithExplicitThrowPointRule
	{
		return new PHPStan\Rules\Exceptions\ThrowsVoidMethodWithExplicitThrowPointRule(
			$this->getService('exceptionTypeResolver'),
			$this->getParameter('exceptions')['check']['missingCheckedExceptionInThrows']
		);
	}


	public function createService0610(): PHPStan\Rules\Exceptions\OverwrittenExitPointByFinallyRule
	{
		return new PHPStan\Rules\Exceptions\OverwrittenExitPointByFinallyRule;
	}


	public function createService0611(): PHPStan\Rules\Arrays\OffsetAccessAssignOpRule
	{
		return new PHPStan\Rules\Arrays\OffsetAccessAssignOpRule($this->getService('0118'));
	}


	public function createService0612(): PHPStan\Rules\Arrays\OffsetAccessAssignmentRule
	{
		return new PHPStan\Rules\Arrays\OffsetAccessAssignmentRule($this->getService('0118'));
	}


	public function createService0613(): PHPStan\Rules\Arrays\ArrayDestructuringRule
	{
		return new PHPStan\Rules\Arrays\ArrayDestructuringRule($this->getService('0118'), $this->getService('0122'));
	}


	public function createService0614(): PHPStan\Rules\Arrays\DuplicateKeysInLiteralArraysRule
	{
		return new PHPStan\Rules\Arrays\DuplicateKeysInLiteralArraysRule($this->getService('081'), $this->getService('0558'));
	}


	public function createService0615(): PHPStan\Rules\Arrays\ArrayUnpackingRule
	{
		return new PHPStan\Rules\Arrays\ArrayUnpackingRule($this->getService('0118'));
	}


	public function createService0616(): PHPStan\Rules\Arrays\NonexistentOffsetInArrayDimFetchRule
	{
		return new PHPStan\Rules\Arrays\NonexistentOffsetInArrayDimFetchRule(
			$this->getService('0118'),
			$this->getService('0122'),
			$this->getParameter('reportMaybes')
		);
	}


	public function createService0617(): PHPStan\Rules\Arrays\UnpackIterableInArrayRule
	{
		return new PHPStan\Rules\Arrays\UnpackIterableInArrayRule($this->getService('0118'));
	}


	public function createService0618(): PHPStan\Rules\Arrays\IterableInForeachRule
	{
		return new PHPStan\Rules\Arrays\IterableInForeachRule($this->getService('0118'));
	}


	public function createService0619(): PHPStan\Rules\Arrays\OffsetAccessWithoutDimForReadingRule
	{
		return new PHPStan\Rules\Arrays\OffsetAccessWithoutDimForReadingRule;
	}


	public function createService0620(): PHPStan\Rules\Arrays\OffsetAccessValueAssignmentRule
	{
		return new PHPStan\Rules\Arrays\OffsetAccessValueAssignmentRule($this->getService('0118'));
	}


	public function createService0621(): PHPStan\Rules\Arrays\InvalidKeyInArrayDimFetchRule
	{
		return new PHPStan\Rules\Arrays\InvalidKeyInArrayDimFetchRule(
			$this->getService('0118'),
			$this->getService('03'),
			$this->getParameter('reportMaybes'),
			$this->getParameter('reportNonIntStringArrayKey')
		);
	}


	public function createService0622(): PHPStan\Rules\Arrays\DeadForeachRule
	{
		return new PHPStan\Rules\Arrays\DeadForeachRule;
	}


	public function createService0623(): PHPStan\Rules\Arrays\InvalidKeyInArrayItemRule
	{
		return new PHPStan\Rules\Arrays\InvalidKeyInArrayItemRule(
			$this->getService('0118'),
			$this->getService('03'),
			$this->getParameter('reportNonIntStringArrayKey')
		);
	}


	public function createService0624(): PHPStan\Rules\Ignore\IgnoreParseErrorRule
	{
		return new PHPStan\Rules\Ignore\IgnoreParseErrorRule;
	}


	public function createService0625(): PHPStan\Rules\Names\UsedNamesRule
	{
		return new PHPStan\Rules\Names\UsedNamesRule;
	}


	public function createService0626(): PHPStan\Rules\Variables\ParameterOutExecutionEndTypeRule
	{
		return new PHPStan\Rules\Variables\ParameterOutExecutionEndTypeRule($this->getService('0125'));
	}


	public function createService0627(): PHPStan\Rules\Variables\InvalidVariableAssignRule
	{
		return new PHPStan\Rules\Variables\InvalidVariableAssignRule;
	}


	public function createService0628(): PHPStan\Rules\Variables\CompactVariablesRule
	{
		return new PHPStan\Rules\Variables\CompactVariablesRule($this->getParameter('checkMaybeUndefinedVariables'));
	}


	public function createService0629(): PHPStan\Rules\Variables\NullCoalesceRule
	{
		return new PHPStan\Rules\Variables\NullCoalesceRule(
			$this->getService('0147'),
			$this->getService('0127'),
			$this->getParameter('featureToggles')['unnecessaryNullCoalesce']
		);
	}


	public function createService0630(): PHPStan\Rules\Variables\UnsetRule
	{
		return new PHPStan\Rules\Variables\UnsetRule($this->getService('0133'), $this->getService('03'));
	}


	public function createService0631(): PHPStan\Rules\Variables\DefinedVariableRule
	{
		return new PHPStan\Rules\Variables\DefinedVariableRule(
			$this->getService('0123'),
			$this->getParameter('cliArgumentsVariablesRegistered'),
			$this->getParameter('checkMaybeUndefinedVariables')
		);
	}


	public function createService0632(): PHPStan\Rules\Variables\ThisInGlobalStatementRule
	{
		return new PHPStan\Rules\Variables\ThisInGlobalStatementRule;
	}


	public function createService0633(): PHPStan\Rules\Variables\EmptyRule
	{
		return new PHPStan\Rules\Variables\EmptyRule($this->getService('0147'), $this->getService('0127'));
	}


	public function createService0634(): PHPStan\Rules\Variables\VariableCloningRule
	{
		return new PHPStan\Rules\Variables\VariableCloningRule($this->getService('0118'));
	}


	public function createService0635(): PHPStan\Rules\Variables\ParameterOutAssignedTypeRule
	{
		return new PHPStan\Rules\Variables\ParameterOutAssignedTypeRule($this->getService('0125'));
	}


	public function createService0636(): PHPStan\Rules\Variables\IssetRule
	{
		return new PHPStan\Rules\Variables\IssetRule($this->getService('0147'), $this->getService('0127'));
	}


	public function createService0637(): PHPStan\Rules\Variables\ThisInStaticStatementRule
	{
		return new PHPStan\Rules\Variables\ThisInStaticStatementRule;
	}


	public function createService0638(): PHPStan\Rules\Functions\ParameterCastableToStringRule
	{
		return new PHPStan\Rules\Functions\ParameterCastableToStringRule(
			$this->getService('reflectionProvider'),
			$this->getService('0148')
		);
	}


	public function createService0639(): PHPStan\Rules\Functions\IncompatibleArrowFunctionDefaultParameterTypeRule
	{
		return new PHPStan\Rules\Functions\IncompatibleArrowFunctionDefaultParameterTypeRule;
	}


	public function createService0640(): PHPStan\Rules\Functions\ClosureReturnTypeRule
	{
		return new PHPStan\Rules\Functions\ClosureReturnTypeRule($this->getService('0175'));
	}


	public function createService0641(): PHPStan\Rules\Functions\CallToFunctionStatementWithoutSideEffectsRule
	{
		return new PHPStan\Rules\Functions\CallToFunctionStatementWithoutSideEffectsRule($this->getService('reflectionProvider'));
	}


	public function createService0642(): PHPStan\Rules\Functions\CallCallablesRule
	{
		return new PHPStan\Rules\Functions\CallCallablesRule(
			$this->getService('0146'),
			$this->getService('0118'),
			$this->getParameter('reportMaybes')
		);
	}


	public function createService0643(): PHPStan\Rules\Functions\ImplodeParameterCastableToStringRule
	{
		return new PHPStan\Rules\Functions\ImplodeParameterCastableToStringRule(
			$this->getService('reflectionProvider'),
			$this->getService('0148')
		);
	}


	public function createService0644(): PHPStan\Rules\Functions\ArrowFunctionReturnNullsafeByRefRule
	{
		return new PHPStan\Rules\Functions\ArrowFunctionReturnNullsafeByRefRule($this->getService('0126'));
	}


	public function createService0645(): PHPStan\Rules\Functions\DefineParametersRule
	{
		return new PHPStan\Rules\Functions\DefineParametersRule($this->getService('03'));
	}


	public function createService0646(): PHPStan\Rules\Functions\ArrowFunctionReturnTypeRule
	{
		return new PHPStan\Rules\Functions\ArrowFunctionReturnTypeRule($this->getService('0175'));
	}


	public function createService0647(): PHPStan\Rules\Functions\CallToNonExistentFunctionRule
	{
		return new PHPStan\Rules\Functions\CallToNonExistentFunctionRule(
			$this->getService('reflectionProvider'),
			$this->getParameter('checkFunctionNameCase'),
			$this->getParameter('tips')['discoveringSymbols']
		);
	}


	public function createService0648(): PHPStan\Rules\Functions\ArrayFilterRule
	{
		return new PHPStan\Rules\Functions\ArrayFilterRule(
			$this->getService('reflectionProvider'),
			$this->getParameter('treatPhpDocTypesAsCertain'),
			$this->getParameter('tips')['treatPhpDocTypesAsCertain']
		);
	}


	public function createService0649(): PHPStan\Rules\Functions\ReturnTypeAfterFinallyRule
	{
		return new PHPStan\Rules\Functions\ReturnTypeAfterFinallyRule($this->getService('0118'));
	}


	public function createService0650(): PHPStan\Rules\Functions\IncompatibleDefaultParameterTypeRule
	{
		return new PHPStan\Rules\Functions\IncompatibleDefaultParameterTypeRule;
	}


	public function createService0651(): PHPStan\Rules\Functions\ReturnTypeRule
	{
		return new PHPStan\Rules\Functions\ReturnTypeRule($this->getService('0175'));
	}


	public function createService0652(): PHPStan\Rules\Functions\InvalidParameterNameRule
	{
		return new PHPStan\Rules\Functions\InvalidParameterNameRule;
	}


	public function createService0653(): PHPStan\Rules\Functions\ExistingClassesInClosureTypehintsRule
	{
		return new PHPStan\Rules\Functions\ExistingClassesInClosureTypehintsRule($this->getService('0183'));
	}


	public function createService0654(): PHPStan\Rules\Functions\ExistingClassesInTypehintsRule
	{
		return new PHPStan\Rules\Functions\ExistingClassesInTypehintsRule($this->getService('0183'));
	}


	public function createService0655(): PHPStan\Rules\Functions\InvalidLexicalVariablesInClosureUseRule
	{
		return new PHPStan\Rules\Functions\InvalidLexicalVariablesInClosureUseRule;
	}


	public function createService0656(): PHPStan\Rules\Functions\InnerFunctionRule
	{
		return new PHPStan\Rules\Functions\InnerFunctionRule;
	}


	public function createService0657(): PHPStan\Rules\Functions\SortParameterCastableToStringRule
	{
		return new PHPStan\Rules\Functions\SortParameterCastableToStringRule(
			$this->getService('reflectionProvider'),
			$this->getService('0148')
		);
	}


	public function createService0658(): PHPStan\Rules\Functions\ParamAttributesRule
	{
		return new PHPStan\Rules\Functions\ParamAttributesRule($this->getService('0167'));
	}


	public function createService0659(): PHPStan\Rules\Functions\UnusedClosureUsesRule
	{
		return new PHPStan\Rules\Functions\UnusedClosureUsesRule(
			$this->getParameter('featureToggles')['reportPreciseLineForUnusedFunctionParameter'],
			$this->getParameter('featureToggles')['unusedParameters']
		);
	}


	public function createService0660(): PHPStan\Rules\Functions\ArrayValuesRule
	{
		return new PHPStan\Rules\Functions\ArrayValuesRule(
			$this->getService('reflectionProvider'),
			$this->getParameter('treatPhpDocTypesAsCertain'),
			$this->getParameter('tips')['treatPhpDocTypesAsCertain']
		);
	}


	public function createService0661(): PHPStan\Rules\Functions\PrintfArrayParametersRule
	{
		return new PHPStan\Rules\Functions\PrintfArrayParametersRule($this->getService('0352'), $this->getService('reflectionProvider'));
	}


	public function createService0662(): PHPStan\Rules\Functions\UselessFunctionReturnValueRule
	{
		return new PHPStan\Rules\Functions\UselessFunctionReturnValueRule($this->getService('reflectionProvider'));
	}


	public function createService0663(): PHPStan\Rules\Functions\IncompatibleClosureDefaultParameterTypeRule
	{
		return new PHPStan\Rules\Functions\IncompatibleClosureDefaultParameterTypeRule;
	}


	public function createService0664(): PHPStan\Rules\Functions\ReturnNullsafeByRefRule
	{
		return new PHPStan\Rules\Functions\ReturnNullsafeByRefRule($this->getService('0126'));
	}


	public function createService0665(): PHPStan\Rules\Functions\CallToFunctionParametersRule
	{
		return new PHPStan\Rules\Functions\CallToFunctionParametersRule(
			$this->getService('reflectionProvider'),
			$this->getService('0146')
		);
	}


	public function createService0666(): PHPStan\Rules\Functions\ClosureAttributesRule
	{
		return new PHPStan\Rules\Functions\ClosureAttributesRule($this->getService('0167'));
	}


	public function createService0667(): PHPStan\Rules\Functions\ArrowFunctionAttributesRule
	{
		return new PHPStan\Rules\Functions\ArrowFunctionAttributesRule($this->getService('0167'));
	}


	public function createService0668(): PHPStan\Rules\Functions\FunctionCallableRule
	{
		return new PHPStan\Rules\Functions\FunctionCallableRule(
			$this->getService('reflectionProvider'),
			$this->getService('0118'),
			$this->getParameter('checkFunctionNameCase'),
			$this->getParameter('reportMaybes')
		);
	}


	public function createService0669(): PHPStan\Rules\Functions\ExistingClassesInArrowFunctionTypehintsRule
	{
		return new PHPStan\Rules\Functions\ExistingClassesInArrowFunctionTypehintsRule($this->getService('0183'));
	}


	public function createService0670(): PHPStan\Rules\Functions\VariadicParametersDeclarationRule
	{
		return new PHPStan\Rules\Functions\VariadicParametersDeclarationRule;
	}


	public function createService0671(): PHPStan\Rules\Functions\PrintfParametersRule
	{
		return new PHPStan\Rules\Functions\PrintfParametersRule($this->getService('0352'), $this->getService('reflectionProvider'));
	}


	public function createService0672(): PHPStan\Rules\Functions\CallToFunctionStatementWithNoDiscardRule
	{
		return new PHPStan\Rules\Functions\CallToFunctionStatementWithNoDiscardRule($this->getService('reflectionProvider'));
	}


	public function createService0673(): PHPStan\Rules\Functions\RandomIntParametersRule
	{
		return new PHPStan\Rules\Functions\RandomIntParametersRule(
			$this->getService('reflectionProvider'),
			$this->getService('03'),
			$this->getParameter('reportMaybes')
		);
	}


	public function createService0674(): PHPStan\Rules\Functions\FilterVarRule
	{
		return new PHPStan\Rules\Functions\FilterVarRule(
			$this->getService('reflectionProvider'),
			$this->getService('0545'),
			$this->getService('0405'),
			$this->getService('03')
		);
	}


	public function createService0675(): PHPStan\Rules\Functions\FunctionAttributesRule
	{
		return new PHPStan\Rules\Functions\FunctionAttributesRule($this->getService('0167'));
	}


	public function createService0676(): PHPStan\Rules\Functions\RedefinedParametersRule
	{
		return new PHPStan\Rules\Functions\RedefinedParametersRule;
	}


	public function createService0677(): PHPStan\Rules\Functions\CallUserFuncRule
	{
		return new PHPStan\Rules\Functions\CallUserFuncRule($this->getService('reflectionProvider'), $this->getService('0146'));
	}


	public function createService0678(): PHPStan\Rules\Comparison\ImpossibleCheckTypeStaticMethodCallRule
	{
		return new PHPStan\Rules\Comparison\ImpossibleCheckTypeStaticMethodCallRule(
			$this->getService('0128'),
			$this->getService('0127'),
			$this->getService('0130'),
			$this->getParameter('treatPhpDocTypesAsCertain'),
			$this->getParameter('reportAlwaysTrueInLastCondition'),
			$this->getParameter('tips')['treatPhpDocTypesAsCertain']
		);
	}


	public function createService0679(): PHPStan\Rules\Comparison\ConstantLooseComparisonRule
	{
		return new PHPStan\Rules\Comparison\ConstantLooseComparisonRule(
			$this->getService('0127'),
			$this->getParameter('treatPhpDocTypesAsCertain'),
			$this->getParameter('reportAlwaysTrueInLastCondition'),
			$this->getParameter('tips')['treatPhpDocTypesAsCertain']
		);
	}


	public function createService0680(): PHPStan\Rules\Comparison\BooleanOrConstantConditionRule
	{
		return new PHPStan\Rules\Comparison\BooleanOrConstantConditionRule(
			$this->getService('0129'),
			$this->getService('0127'),
			$this->getService('0130'),
			$this->getParameter('treatPhpDocTypesAsCertain'),
			$this->getParameter('reportAlwaysTrueInLastCondition'),
			$this->getParameter('tips')['treatPhpDocTypesAsCertain']
		);
	}


	public function createService0681(): PHPStan\Rules\Comparison\DoWhileLoopConstantConditionRule
	{
		return new PHPStan\Rules\Comparison\DoWhileLoopConstantConditionRule(
			$this->getService('0129'),
			$this->getService('0127'),
			$this->getService('0130'),
			$this->getParameter('treatPhpDocTypesAsCertain'),
			$this->getParameter('tips')['treatPhpDocTypesAsCertain']
		);
	}


	public function createService0682(): PHPStan\Rules\Comparison\ImpossibleCheckTypeMethodCallRule
	{
		return new PHPStan\Rules\Comparison\ImpossibleCheckTypeMethodCallRule(
			$this->getService('0128'),
			$this->getService('0127'),
			$this->getService('0130'),
			$this->getParameter('treatPhpDocTypesAsCertain'),
			$this->getParameter('reportAlwaysTrueInLastCondition'),
			$this->getParameter('tips')['treatPhpDocTypesAsCertain']
		);
	}


	public function createService0683(): PHPStan\Rules\Comparison\ElseIfConstantConditionRule
	{
		return new PHPStan\Rules\Comparison\ElseIfConstantConditionRule(
			$this->getService('0129'),
			$this->getService('0127'),
			$this->getService('0130'),
			$this->getParameter('treatPhpDocTypesAsCertain'),
			$this->getParameter('reportAlwaysTrueInLastCondition'),
			$this->getParameter('tips')['treatPhpDocTypesAsCertain']
		);
	}


	public function createService0684(): PHPStan\Rules\Comparison\ImpossibleCheckTypeFunctionCallRule
	{
		return new PHPStan\Rules\Comparison\ImpossibleCheckTypeFunctionCallRule(
			$this->getService('0128'),
			$this->getService('0127'),
			$this->getService('0130'),
			$this->getParameter('treatPhpDocTypesAsCertain'),
			$this->getParameter('reportAlwaysTrueInLastCondition'),
			$this->getParameter('tips')['treatPhpDocTypesAsCertain']
		);
	}


	public function createService0685(): PHPStan\Rules\Comparison\BooleanAndConstantConditionRule
	{
		return new PHPStan\Rules\Comparison\BooleanAndConstantConditionRule(
			$this->getService('0129'),
			$this->getService('0127'),
			$this->getService('0130'),
			$this->getParameter('treatPhpDocTypesAsCertain'),
			$this->getParameter('reportAlwaysTrueInLastCondition'),
			$this->getParameter('tips')['treatPhpDocTypesAsCertain']
		);
	}


	public function createService0686(): PHPStan\Rules\Comparison\UsageOfVoidMatchExpressionRule
	{
		return new PHPStan\Rules\Comparison\UsageOfVoidMatchExpressionRule;
	}


	public function createService0687(): PHPStan\Rules\Comparison\NumberComparisonOperatorsConstantConditionRule
	{
		return new PHPStan\Rules\Comparison\NumberComparisonOperatorsConstantConditionRule(
			$this->getService('0127'),
			$this->getParameter('treatPhpDocTypesAsCertain'),
			$this->getParameter('tips')['treatPhpDocTypesAsCertain']
		);
	}


	public function createService0688(): PHPStan\Rules\Comparison\LogicalXorConstantConditionRule
	{
		return new PHPStan\Rules\Comparison\LogicalXorConstantConditionRule(
			$this->getService('0129'),
			$this->getService('0127'),
			$this->getService('0130'),
			$this->getParameter('treatPhpDocTypesAsCertain'),
			$this->getParameter('reportAlwaysTrueInLastCondition'),
			$this->getParameter('tips')['treatPhpDocTypesAsCertain']
		);
	}


	public function createService0689(): PHPStan\Rules\Comparison\IfConstantConditionRule
	{
		return new PHPStan\Rules\Comparison\IfConstantConditionRule(
			$this->getService('0129'),
			$this->getService('0127'),
			$this->getService('0130'),
			$this->getParameter('treatPhpDocTypesAsCertain'),
			$this->getParameter('tips')['treatPhpDocTypesAsCertain']
		);
	}


	public function createService0690(): PHPStan\Rules\Comparison\ConstantConditionInTraitRule
	{
		return new PHPStan\Rules\Comparison\ConstantConditionInTraitRule;
	}


	public function createService0691(): PHPStan\Rules\Comparison\FunctionCallConstantConditionRule
	{
		return new PHPStan\Rules\Comparison\FunctionCallConstantConditionRule;
	}


	public function createService0692(): PHPStan\Rules\Comparison\MatchExpressionRule
	{
		return new PHPStan\Rules\Comparison\MatchExpressionRule(
			$this->getService('0129'),
			$this->getService('0127'),
			$this->getService('0130'),
			$this->getParameter('treatPhpDocTypesAsCertain')
		);
	}


	public function createService0693(): PHPStan\Rules\Comparison\BooleanNotConstantConditionRule
	{
		return new PHPStan\Rules\Comparison\BooleanNotConstantConditionRule(
			$this->getService('0129'),
			$this->getService('0127'),
			$this->getService('0130'),
			$this->getParameter('treatPhpDocTypesAsCertain'),
			$this->getParameter('reportAlwaysTrueInLastCondition'),
			$this->getParameter('tips')['treatPhpDocTypesAsCertain']
		);
	}


	public function createService0694(): PHPStan\Rules\Comparison\StrictComparisonOfDifferentTypesRule
	{
		return new PHPStan\Rules\Comparison\StrictComparisonOfDifferentTypesRule(
			$this->getService('0326'),
			$this->getService('0127'),
			$this->getParameter('treatPhpDocTypesAsCertain'),
			$this->getParameter('reportAlwaysTrueInLastCondition'),
			$this->getParameter('tips')['treatPhpDocTypesAsCertain']
		);
	}


	public function createService0695(): PHPStan\Rules\Comparison\WhileLoopAlwaysTrueConditionRule
	{
		return new PHPStan\Rules\Comparison\WhileLoopAlwaysTrueConditionRule(
			$this->getService('0129'),
			$this->getService('0127'),
			$this->getService('0130'),
			$this->getParameter('treatPhpDocTypesAsCertain'),
			$this->getParameter('tips')['treatPhpDocTypesAsCertain']
		);
	}


	public function createService0696(): PHPStan\Rules\Comparison\TernaryOperatorConstantConditionRule
	{
		return new PHPStan\Rules\Comparison\TernaryOperatorConstantConditionRule(
			$this->getService('0129'),
			$this->getService('0127'),
			$this->getService('0130'),
			$this->getParameter('treatPhpDocTypesAsCertain'),
			$this->getParameter('tips')['treatPhpDocTypesAsCertain']
		);
	}


	public function createService0697(): PHPStan\Rules\Comparison\WhileLoopAlwaysFalseConditionRule
	{
		return new PHPStan\Rules\Comparison\WhileLoopAlwaysFalseConditionRule(
			$this->getService('0129'),
			$this->getService('0127'),
			$this->getService('0130'),
			$this->getParameter('treatPhpDocTypesAsCertain'),
			$this->getParameter('tips')['treatPhpDocTypesAsCertain']
		);
	}


	public function createService0698(): PHPStan\Rules\Types\InvalidTypesInUnionRule
	{
		return new PHPStan\Rules\Types\InvalidTypesInUnionRule;
	}


	public function createService0699(): PHPStan\Rules\Keywords\DeclareStrictTypesRule
	{
		return new PHPStan\Rules\Keywords\DeclareStrictTypesRule($this->getService('081'));
	}


	public function createService0700(): PHPStan\Rules\Keywords\GotoUndefinedLabelRule
	{
		return new PHPStan\Rules\Keywords\GotoUndefinedLabelRule;
	}


	public function createService0701(): PHPStan\Rules\Keywords\RequireFileExistsRule
	{
		return new PHPStan\Rules\Keywords\RequireFileExistsRule(
			$this->getService('081'),
			$this->getParameter('featureToggles')['magicDirInInclude'],
			$this->getService('09'),
			$this->getService('010')
		);
	}


	public function createService0702(): PHPStan\Rules\Keywords\ContinueBreakInLoopRule
	{
		return new PHPStan\Rules\Keywords\ContinueBreakInLoopRule;
	}


	public function createService0703(): PHPStan\Rules\Properties\AccessPropertiesRule
	{
		return new PHPStan\Rules\Properties\AccessPropertiesRule($this->getService('0132'));
	}


	public function createService0704(): PHPStan\Rules\Properties\DefaultValueTypesAssignedToPropertiesRule
	{
		return new PHPStan\Rules\Properties\DefaultValueTypesAssignedToPropertiesRule($this->getService('0118'));
	}


	public function createService0705(): PHPStan\Rules\Properties\AccessPropertiesInAssignRule
	{
		return new PHPStan\Rules\Properties\AccessPropertiesInAssignRule($this->getService('0132'));
	}


	public function createService0706(): PHPStan\Rules\Properties\ReadOnlyByPhpDocPropertyAssignRefRule
	{
		return new PHPStan\Rules\Properties\ReadOnlyByPhpDocPropertyAssignRefRule($this->getService('0133'));
	}


	public function createService0707(): PHPStan\Rules\Properties\PropertyAssignRefRule
	{
		return new PHPStan\Rules\Properties\PropertyAssignRefRule($this->getService('03'), $this->getService('0133'));
	}


	public function createService0708(): PHPStan\Rules\Properties\ReadingWriteOnlyPropertiesRule
	{
		return new PHPStan\Rules\Properties\ReadingWriteOnlyPropertiesRule(
			$this->getService('0131'),
			$this->getService('0133'),
			$this->getService('0118'),
			$this->getParameter('checkThisOnly')
		);
	}


	public function createService0709(): PHPStan\Rules\Properties\ExistingClassesInPropertiesRule
	{
		return new PHPStan\Rules\Properties\ExistingClassesInPropertiesRule(
			$this->getService('reflectionProvider'),
			$this->getService('0124'),
			$this->getService('0114'),
			$this->getService('03'),
			$this->getParameter('checkClassCaseSensitivity'),
			$this->getParameter('checkThisOnly'),
			$this->getParameter('tips')['discoveringSymbols']
		);
	}


	public function createService0710(): PHPStan\Rules\Properties\PropertyInClassRule
	{
		return new PHPStan\Rules\Properties\PropertyInClassRule;
	}


	public function createService0711(): PHPStan\Rules\Properties\PropertyHookAttributesRule
	{
		return new PHPStan\Rules\Properties\PropertyHookAttributesRule($this->getService('0167'));
	}


	public function createService0712(): PHPStan\Rules\Properties\ReadOnlyByPhpDocPropertyRule
	{
		return new PHPStan\Rules\Properties\ReadOnlyByPhpDocPropertyRule;
	}


	public function createService0713(): PHPStan\Rules\Properties\MissingReadOnlyByPhpDocPropertyAssignRule
	{
		return new PHPStan\Rules\Properties\MissingReadOnlyByPhpDocPropertyAssignRule($this->getService('049'));
	}


	public function createService0714(): PHPStan\Rules\Properties\PropertiesInInterfaceRule
	{
		return new PHPStan\Rules\Properties\PropertiesInInterfaceRule;
	}


	public function createService0715(): PHPStan\Rules\Properties\ReadOnlyByPhpDocPropertyAssignRule
	{
		return new PHPStan\Rules\Properties\ReadOnlyByPhpDocPropertyAssignRule($this->getService('0133'), $this->getService('049'));
	}


	public function createService0716(): PHPStan\Rules\Properties\GetNonVirtualPropertyHookReadRule
	{
		return new PHPStan\Rules\Properties\GetNonVirtualPropertyHookReadRule;
	}


	public function createService0717(): PHPStan\Rules\Properties\AccessPrivatePropertyThroughStaticRule
	{
		return new PHPStan\Rules\Properties\AccessPrivatePropertyThroughStaticRule;
	}


	public function createService0718(): PHPStan\Rules\Properties\PropertyAttributesRule
	{
		return new PHPStan\Rules\Properties\PropertyAttributesRule($this->getService('0167'));
	}


	public function createService0719(): PHPStan\Rules\Properties\SetNonVirtualPropertyHookAssignRule
	{
		return new PHPStan\Rules\Properties\SetNonVirtualPropertyHookAssignRule;
	}


	public function createService0720(): PHPStan\Rules\Properties\SetPropertyHookParameterRule
	{
		return new PHPStan\Rules\Properties\SetPropertyHookParameterRule(
			$this->getService('0153'),
			$this->getParameter('checkPhpDocMethodSignatures'),
			$this->getParameter('checkMissingTypehints')
		);
	}


	public function createService0721(): PHPStan\Rules\Properties\OverridingPropertyRule
	{
		return new PHPStan\Rules\Properties\OverridingPropertyRule(
			$this->getService('03'),
			$this->getParameter('checkPhpDocMethodSignatures'),
			$this->getParameter('reportMaybesInPropertyPhpDocTypes'),
			$this->getParameter('checkMissingOverridePropertyAttribute'),
			$this->getParameter('checkMissingOverrideMethodAttribute')
		);
	}


	public function createService0722(): PHPStan\Rules\Properties\ReadOnlyPropertyAssignRule
	{
		return new PHPStan\Rules\Properties\ReadOnlyPropertyAssignRule(
			$this->getService('0133'),
			$this->getService('049'),
			$this->getService('03')
		);
	}


	public function createService0723(): PHPStan\Rules\Properties\WritingToReadOnlyPropertiesRule
	{
		return new PHPStan\Rules\Properties\WritingToReadOnlyPropertiesRule(
			$this->getService('0118'),
			$this->getService('0131'),
			$this->getService('0133'),
			$this->getParameter('checkThisOnly')
		);
	}


	public function createService0724(): PHPStan\Rules\Properties\InvalidCallablePropertyTypeRule
	{
		return new PHPStan\Rules\Properties\InvalidCallablePropertyTypeRule;
	}


	public function createService0725(): PHPStan\Rules\Properties\MissingReadOnlyPropertyAssignRule
	{
		return new PHPStan\Rules\Properties\MissingReadOnlyPropertyAssignRule($this->getService('049'));
	}


	public function createService0726(): PHPStan\Rules\Properties\ReadOnlyPropertyRule
	{
		return new PHPStan\Rules\Properties\ReadOnlyPropertyRule;
	}


	public function createService0727(): PHPStan\Rules\Properties\AccessStaticPropertiesInAssignRule
	{
		return new PHPStan\Rules\Properties\AccessStaticPropertiesInAssignRule($this->getService('0134'));
	}


	public function createService0728(): PHPStan\Rules\Properties\ReadOnlyPropertyAssignRefRule
	{
		return new PHPStan\Rules\Properties\ReadOnlyPropertyAssignRefRule($this->getService('0133'));
	}


	public function createService0729(): PHPStan\Rules\Properties\TypesAssignedToPropertiesRule
	{
		return new PHPStan\Rules\Properties\TypesAssignedToPropertiesRule($this->getService('0118'), $this->getService('0133'));
	}


	public function createService0730(): PHPStan\Rules\Properties\ExistingClassesInPropertyHookTypehintsRule
	{
		return new PHPStan\Rules\Properties\ExistingClassesInPropertyHookTypehintsRule($this->getService('0183'));
	}


	public function createService0731(): PHPStan\Rules\Properties\AccessStaticPropertiesRule
	{
		return new PHPStan\Rules\Properties\AccessStaticPropertiesRule($this->getService('0134'));
	}


	public function createService0732(): PHPStan\Rules\Properties\NullsafePropertyFetchRule
	{
		return new PHPStan\Rules\Properties\NullsafePropertyFetchRule(
			$this->getParameter('treatPhpDocTypesAsCertain'),
			$this->getParameter('tips')['treatPhpDocTypesAsCertain']
		);
	}


	public function createService0733(): PHPStan\Rules\Classes\RequireExtendsRule
	{
		return new PHPStan\Rules\Classes\RequireExtendsRule;
	}


	public function createService0734(): PHPStan\Rules\Classes\AllowedSubTypesRule
	{
		return new PHPStan\Rules\Classes\AllowedSubTypesRule;
	}


	public function createService0735(): PHPStan\Rules\Classes\TraitAttributeClassRule
	{
		return new PHPStan\Rules\Classes\TraitAttributeClassRule;
	}


	public function createService0736(): PHPStan\Rules\Classes\EnumSanityRule
	{
		return new PHPStan\Rules\Classes\EnumSanityRule($this->getService('072'));
	}


	public function createService0737(): PHPStan\Rules\Classes\InstantiationCallableRule
	{
		return new PHPStan\Rules\Classes\InstantiationCallableRule;
	}


	public function createService0738(): PHPStan\Rules\Classes\MethodTagRule
	{
		return new PHPStan\Rules\Classes\MethodTagRule($this->getService('0144'));
	}


	public function createService0739(): PHPStan\Rules\Classes\ClassConstantAttributesRule
	{
		return new PHPStan\Rules\Classes\ClassConstantAttributesRule($this->getService('0167'));
	}


	public function createService0740(): PHPStan\Rules\Classes\ExistingClassInTraitUseRule
	{
		return new PHPStan\Rules\Classes\ExistingClassInTraitUseRule(
			$this->getService('0124'),
			$this->getService('reflectionProvider'),
			$this->getParameter('tips')['discoveringSymbols']
		);
	}


	public function createService0741(): PHPStan\Rules\Classes\NewStaticRule
	{
		return new PHPStan\Rules\Classes\NewStaticRule($this->getService('03'), $this->getService('0145'));
	}


	public function createService0742(): PHPStan\Rules\Classes\LocalTypeAliasesRule
	{
		return new PHPStan\Rules\Classes\LocalTypeAliasesRule($this->getService('0140'));
	}


	public function createService0743(): PHPStan\Rules\Classes\LocalTypeTraitUseAliasesRule
	{
		return new PHPStan\Rules\Classes\LocalTypeTraitUseAliasesRule($this->getService('0140'));
	}


	public function createService0744(): PHPStan\Rules\Classes\MixinTraitUseRule
	{
		return new PHPStan\Rules\Classes\MixinTraitUseRule($this->getService('0142'));
	}


	public function createService0745(): PHPStan\Rules\Classes\InvalidPromotedPropertiesRule
	{
		return new PHPStan\Rules\Classes\InvalidPromotedPropertiesRule;
	}


	public function createService0746(): PHPStan\Rules\Classes\ExistingClassInInstanceOfRule
	{
		return new PHPStan\Rules\Classes\ExistingClassInInstanceOfRule(
			$this->getService('reflectionProvider'),
			$this->getService('0124'),
			$this->getParameter('checkClassCaseSensitivity'),
			$this->getParameter('tips')['discoveringSymbols']
		);
	}


	public function createService0747(): PHPStan\Rules\Classes\LocalTypeTraitAliasesRule
	{
		return new PHPStan\Rules\Classes\LocalTypeTraitAliasesRule($this->getService('0140'), $this->getService('reflectionProvider'));
	}


	public function createService0748(): PHPStan\Rules\Classes\ReadOnlyClassRule
	{
		return new PHPStan\Rules\Classes\ReadOnlyClassRule;
	}


	public function createService0749(): PHPStan\Rules\Classes\InstantiationRule
	{
		return new PHPStan\Rules\Classes\InstantiationRule(
			$this->getService('phpstan.extensionsCollection.PHPStan.Rules.RestrictedUsage.RestrictedMethodUsageExtension'),
			$this->getService('reflectionProvider'),
			$this->getService('0146'),
			$this->getService('0124'),
			$this->getService('0118'),
			$this->getService('0145'),
			$this->getParameter('featureToggles')['newOnNonObject'],
			$this->getParameter('tips')['discoveringSymbols']
		);
	}


	public function createService0750(): PHPStan\Rules\Classes\DuplicateDeclarationRule
	{
		return new PHPStan\Rules\Classes\DuplicateDeclarationRule($this->getService('0143'));
	}


	public function createService0751(): PHPStan\Rules\Classes\AccessPrivateConstantThroughStaticRule
	{
		return new PHPStan\Rules\Classes\AccessPrivateConstantThroughStaticRule;
	}


	public function createService0752(): PHPStan\Rules\Classes\DuplicateTraitDeclarationRule
	{
		return new PHPStan\Rules\Classes\DuplicateTraitDeclarationRule($this->getService('0143'));
	}


	public function createService0753(): PHPStan\Rules\Classes\PropertyTagRule
	{
		return new PHPStan\Rules\Classes\PropertyTagRule($this->getService('0141'));
	}


	public function createService0754(): PHPStan\Rules\Classes\NonClassAttributeClassRule
	{
		return new PHPStan\Rules\Classes\NonClassAttributeClassRule;
	}


	public function createService0755(): PHPStan\Rules\Classes\ExistingClassesInInterfaceExtendsRule
	{
		return new PHPStan\Rules\Classes\ExistingClassesInInterfaceExtendsRule(
			$this->getService('0124'),
			$this->getService('reflectionProvider'),
			$this->getParameter('tips')['discoveringSymbols']
		);
	}


	public function createService0756(): PHPStan\Rules\Classes\MethodTagTraitUseRule
	{
		return new PHPStan\Rules\Classes\MethodTagTraitUseRule($this->getService('0144'));
	}


	public function createService0757(): PHPStan\Rules\Classes\MixinRule
	{
		return new PHPStan\Rules\Classes\MixinRule($this->getService('0142'));
	}


	public function createService0758(): PHPStan\Rules\Classes\ClassAttributesRule
	{
		return new PHPStan\Rules\Classes\ClassAttributesRule($this->getService('0167'));
	}


	public function createService0759(): PHPStan\Rules\Classes\PropertyTagTraitUseRule
	{
		return new PHPStan\Rules\Classes\PropertyTagTraitUseRule($this->getService('0141'));
	}


	public function createService0760(): PHPStan\Rules\Classes\PropertyTagTraitRule
	{
		return new PHPStan\Rules\Classes\PropertyTagTraitRule($this->getService('0141'), $this->getService('reflectionProvider'));
	}


	public function createService0761(): PHPStan\Rules\Classes\ClassConstantRule
	{
		return new PHPStan\Rules\Classes\ClassConstantRule(
			$this->getService('reflectionProvider'),
			$this->getService('0118'),
			$this->getService('0124'),
			$this->getService('0123')
		);
	}


	public function createService0762(): PHPStan\Rules\Classes\RequireImplementsRule
	{
		return new PHPStan\Rules\Classes\RequireImplementsRule;
	}


	public function createService0763(): PHPStan\Rules\Classes\MixinTraitRule
	{
		return new PHPStan\Rules\Classes\MixinTraitRule($this->getService('0142'), $this->getService('reflectionProvider'));
	}


	public function createService0764(): PHPStan\Rules\Classes\ImpossibleInstanceOfRule
	{
		return new PHPStan\Rules\Classes\ImpossibleInstanceOfRule(
			$this->getService('0118'),
			$this->getService('0127'),
			$this->getParameter('treatPhpDocTypesAsCertain'),
			$this->getParameter('reportAlwaysTrueInLastCondition'),
			$this->getParameter('tips')['treatPhpDocTypesAsCertain']
		);
	}


	public function createService0765(): PHPStan\Rules\Classes\ExistingClassesInEnumImplementsRule
	{
		return new PHPStan\Rules\Classes\ExistingClassesInEnumImplementsRule(
			$this->getService('0124'),
			$this->getService('reflectionProvider'),
			$this->getParameter('tips')['discoveringSymbols']
		);
	}


	public function createService0766(): PHPStan\Rules\Classes\UnusedConstructorParametersRule
	{
		return new PHPStan\Rules\Classes\UnusedConstructorParametersRule(
			$this->getService('0152'),
			$this->getParameter('featureToggles')['reportPreciseLineForUnusedFunctionParameter'],
			$this->getParameter('featureToggles')['unusedParameters']
		);
	}


	public function createService0767(): PHPStan\Rules\Classes\ExistingClassInClassExtendsRule
	{
		return new PHPStan\Rules\Classes\ExistingClassInClassExtendsRule(
			$this->getService('0124'),
			$this->getService('reflectionProvider'),
			$this->getParameter('tips')['discoveringSymbols']
		);
	}


	public function createService0768(): PHPStan\Rules\Classes\MethodTagTraitRule
	{
		return new PHPStan\Rules\Classes\MethodTagTraitRule($this->getService('0144'), $this->getService('reflectionProvider'));
	}


	public function createService0769(): PHPStan\Rules\Classes\ExistingClassesInClassImplementsRule
	{
		return new PHPStan\Rules\Classes\ExistingClassesInClassImplementsRule(
			$this->getService('0124'),
			$this->getService('reflectionProvider'),
			$this->getParameter('tips')['discoveringSymbols']
		);
	}


	public function createService0770(): PHPStan\Rules\EnumCases\EnumCaseAttributesRule
	{
		return new PHPStan\Rules\EnumCases\EnumCaseAttributesRule($this->getService('0167'));
	}


	public function createService0771(): PHPStan\Rules\EnumCases\EnumCaseOutsideEnumRule
	{
		return new PHPStan\Rules\EnumCases\EnumCaseOutsideEnumRule;
	}


	public function createService0772(): PHPStan\Rules\Traits\ConflictingTraitConstantsRule
	{
		return new PHPStan\Rules\Traits\ConflictingTraitConstantsRule($this->getService('072'), $this->getService('reflectionProvider'));
	}


	public function createService0773(): PHPStan\Rules\Traits\ConstantsInTraitsRule
	{
		return new PHPStan\Rules\Traits\ConstantsInTraitsRule;
	}


	public function createService0774(): PHPStan\Rules\Traits\NotAnalysedTraitRule
	{
		return new PHPStan\Rules\Traits\NotAnalysedTraitRule;
	}


	public function createService0775(): PHPStan\Rules\Traits\TraitAttributesRule
	{
		return new PHPStan\Rules\Traits\TraitAttributesRule($this->getService('0167'));
	}


	public function createService0776(): PHPStan\Rules\Regexp\RegularExpressionQuotingRule
	{
		return new PHPStan\Rules\Regexp\RegularExpressionQuotingRule($this->getService('reflectionProvider'), $this->getService('0345'));
	}


	public function createService0777(): PHPStan\Rules\Regexp\RegularExpressionPatternRule
	{
		return new PHPStan\Rules\Regexp\RegularExpressionPatternRule($this->getService('0345'));
	}


	public function createService0778(): PHPStan\Rules\Api\GetTemplateTypeRule
	{
		return new PHPStan\Rules\Api\GetTemplateTypeRule($this->getService('reflectionProvider'));
	}


	public function createService0779(): PHPStan\Rules\Api\OldPhpParser4ClassRule
	{
		return new PHPStan\Rules\Api\OldPhpParser4ClassRule;
	}


	public function createService0780(): PHPStan\Rules\Api\ApiInterfaceExtendsRule
	{
		return new PHPStan\Rules\Api\ApiInterfaceExtendsRule($this->getService('0149'), $this->getService('reflectionProvider'));
	}


	public function createService0781(): PHPStan\Rules\Api\NodeConnectingVisitorAttributesRule
	{
		return new PHPStan\Rules\Api\NodeConnectingVisitorAttributesRule;
	}


	public function createService0782(): PHPStan\Rules\Api\ApiInstanceofTypeRule
	{
		return new PHPStan\Rules\Api\ApiInstanceofTypeRule($this->getService('reflectionProvider'));
	}


	public function createService0783(): PHPStan\Rules\Api\ApiInstantiationRule
	{
		return new PHPStan\Rules\Api\ApiInstantiationRule($this->getService('0149'), $this->getService('reflectionProvider'));
	}


	public function createService0784(): PHPStan\Rules\Api\ApiClassConstFetchRule
	{
		return new PHPStan\Rules\Api\ApiClassConstFetchRule($this->getService('0149'), $this->getService('reflectionProvider'));
	}


	public function createService0785(): PHPStan\Rules\Api\ApiStaticCallRule
	{
		return new PHPStan\Rules\Api\ApiStaticCallRule($this->getService('0149'), $this->getService('reflectionProvider'));
	}


	public function createService0786(): PHPStan\Rules\Api\ApiMethodCallRule
	{
		return new PHPStan\Rules\Api\ApiMethodCallRule($this->getService('0149'));
	}


	public function createService0787(): PHPStan\Rules\Api\ApiInstanceofRule
	{
		return new PHPStan\Rules\Api\ApiInstanceofRule($this->getService('0149'), $this->getService('reflectionProvider'));
	}


	public function createService0788(): PHPStan\Rules\Api\RuntimeReflectionInstantiationRule
	{
		return new PHPStan\Rules\Api\RuntimeReflectionInstantiationRule($this->getService('reflectionProvider'));
	}


	public function createService0789(): PHPStan\Rules\Api\ApiClassExtendsRule
	{
		return new PHPStan\Rules\Api\ApiClassExtendsRule($this->getService('0149'), $this->getService('reflectionProvider'));
	}


	public function createService0790(): PHPStan\Rules\Api\RuntimeReflectionFunctionRule
	{
		return new PHPStan\Rules\Api\RuntimeReflectionFunctionRule($this->getService('reflectionProvider'));
	}


	public function createService0791(): PHPStan\Rules\Api\PhpStanNamespaceIn3rdPartyPackageRule
	{
		return new PHPStan\Rules\Api\PhpStanNamespaceIn3rdPartyPackageRule($this->getService('0149'));
	}


	public function createService0792(): PHPStan\Rules\Api\ApiClassImplementsRule
	{
		return new PHPStan\Rules\Api\ApiClassImplementsRule($this->getService('0149'), $this->getService('reflectionProvider'));
	}


	public function createService0793(): PHPStan\Rules\Api\ApiTraitUseRule
	{
		return new PHPStan\Rules\Api\ApiTraitUseRule($this->getService('0149'), $this->getService('reflectionProvider'));
	}


	public function createService0794(): PHPStan\Rules\Cast\InvalidPartOfEncapsedStringRule
	{
		return new PHPStan\Rules\Cast\InvalidPartOfEncapsedStringRule($this->getService('081'), $this->getService('0118'));
	}


	public function createService0795(): PHPStan\Rules\Cast\UnsetCastRule
	{
		return new PHPStan\Rules\Cast\UnsetCastRule;
	}


	public function createService0796(): PHPStan\Rules\Cast\EchoRule
	{
		return new PHPStan\Rules\Cast\EchoRule($this->getService('0118'));
	}


	public function createService0797(): PHPStan\Rules\Cast\VoidCastRule
	{
		return new PHPStan\Rules\Cast\VoidCastRule;
	}


	public function createService0798(): PHPStan\Rules\Cast\PrintRule
	{
		return new PHPStan\Rules\Cast\PrintRule($this->getService('0118'));
	}


	public function createService0799(): PHPStan\Rules\Cast\InvalidCastRule
	{
		return new PHPStan\Rules\Cast\InvalidCastRule($this->getService('reflectionProvider'), $this->getService('0118'));
	}


	public function createService0800(): PHPStan\Rules\Cast\DeprecatedCastRule
	{
		return new PHPStan\Rules\Cast\DeprecatedCastRule;
	}


	public function createService0801(): PHPStan\Rules\DeadCode\NoopRule
	{
		return new PHPStan\Rules\DeadCode\NoopRule($this->getService('081'));
	}


	public function createService0802(): PHPStan\Rules\DeadCode\UnreachableStatementRule
	{
		return new PHPStan\Rules\DeadCode\UnreachableStatementRule;
	}


	public function createService0803(): PHPStan\Rules\DeadCode\CallToConstructorStatementWithoutImpurePointsRule
	{
		return new PHPStan\Rules\DeadCode\CallToConstructorStatementWithoutImpurePointsRule($this->getService('0151'));
	}


	public function createService0804(): PHPStan\Rules\DeadCode\UnusedPrivateConstantRule
	{
		return new PHPStan\Rules\DeadCode\UnusedPrivateConstantRule($this->getService('phpstan.extensionsCollection.PHPStan.Rules.Constants.AlwaysUsedClassConstantsExtension'));
	}


	public function createService0805(): PHPStan\Rules\DeadCode\UnusedPrivateMethodRule
	{
		return new PHPStan\Rules\DeadCode\UnusedPrivateMethodRule($this->getService('phpstan.extensionsCollection.PHPStan.Rules.Methods.AlwaysUsedMethodExtension'));
	}


	public function createService0806(): PHPStan\Rules\DeadCode\CallToFunctionStatementWithoutImpurePointsRule
	{
		return new PHPStan\Rules\DeadCode\CallToFunctionStatementWithoutImpurePointsRule($this->getService('0151'));
	}


	public function createService0807(): PHPStan\Rules\DeadCode\UnusedPrivatePropertyRule
	{
		return new PHPStan\Rules\DeadCode\UnusedPrivatePropertyRule(
			$this->getService('phpstan.extensionsCollection.PHPStan.Rules.Properties.ReadWritePropertiesExtension'),
			$this->getParameter('propertyAlwaysWrittenTags'),
			$this->getParameter('propertyAlwaysReadTags'),
			$this->getParameter('checkUninitializedProperties')
		);
	}


	public function createService0808(): PHPStan\Rules\DeadCode\CallToMethodStatementWithoutImpurePointsRule
	{
		return new PHPStan\Rules\DeadCode\CallToMethodStatementWithoutImpurePointsRule($this->getService('0151'));
	}


	public function createService0809(): PHPStan\Rules\DeadCode\CallToStaticMethodStatementWithoutImpurePointsRule
	{
		return new PHPStan\Rules\DeadCode\CallToStaticMethodStatementWithoutImpurePointsRule($this->getService('0151'));
	}


	public function createService0810(): PHPStan\Rules\TooWideTypehints\TooWideFunctionReturnTypehintRule
	{
		return new PHPStan\Rules\TooWideTypehints\TooWideFunctionReturnTypehintRule($this->getService('0154'));
	}


	public function createService0811(): PHPStan\Rules\TooWideTypehints\TooWideFunctionParameterOutTypeRule
	{
		return new PHPStan\Rules\TooWideTypehints\TooWideFunctionParameterOutTypeRule($this->getService('0155'));
	}


	public function createService0812(): PHPStan\Rules\TooWideTypehints\TooWideMethodParameterOutTypeRule
	{
		return new PHPStan\Rules\TooWideTypehints\TooWideMethodParameterOutTypeRule(
			$this->getService('0155'),
			$this->getParameter('checkTooWideParameterOutInProtectedAndPublicMethods')
		);
	}


	public function createService0813(): PHPStan\Rules\TooWideTypehints\TooWideArrowFunctionReturnTypehintRule
	{
		return new PHPStan\Rules\TooWideTypehints\TooWideArrowFunctionReturnTypehintRule($this->getService('0154'));
	}


	public function createService0814(): PHPStan\Rules\TooWideTypehints\TooWideClosureReturnTypehintRule
	{
		return new PHPStan\Rules\TooWideTypehints\TooWideClosureReturnTypehintRule($this->getService('0154'));
	}


	public function createService0815(): PHPStan\Rules\TooWideTypehints\TooWidePropertyTypeRule
	{
		return new PHPStan\Rules\TooWideTypehints\TooWidePropertyTypeRule(
			$this->getService('phpstan.extensionsCollection.PHPStan.Rules.Properties.ReadWritePropertiesExtension'),
			$this->getService('0154')
		);
	}


	public function createService0816(): PHPStan\Rules\TooWideTypehints\TooWideMethodReturnTypehintRule
	{
		return new PHPStan\Rules\TooWideTypehints\TooWideMethodReturnTypehintRule(
			$this->getParameter('checkTooWideReturnTypesInProtectedAndPublicMethods'),
			$this->getService('0154')
		);
	}


	public function createService0817(): PHPStan\Rules\Pure\PurePropertyHookRule
	{
		return new PHPStan\Rules\Pure\PurePropertyHookRule($this->getService('0156'));
	}


	public function createService0818(): PHPStan\Rules\Pure\PureFunctionRule
	{
		return new PHPStan\Rules\Pure\PureFunctionRule($this->getService('0156'));
	}


	public function createService0819(): PHPStan\Rules\Pure\PureMethodRule
	{
		return new PHPStan\Rules\Pure\PureMethodRule($this->getService('0156'));
	}


	public function createService0820(): PHPStan\Rules\Constants\ValueAssignedToClassConstantRule
	{
		return new PHPStan\Rules\Constants\ValueAssignedToClassConstantRule(
			$this->getService('0187'),
			$this->getParameter('featureToggles')['checkDynamicConstantNameValues']
		);
	}


	public function createService0821(): PHPStan\Rules\Constants\FinalPrivateConstantRule
	{
		return new PHPStan\Rules\Constants\FinalPrivateConstantRule;
	}


	public function createService0822(): PHPStan\Rules\Constants\DynamicClassConstantFetchRule
	{
		return new PHPStan\Rules\Constants\DynamicClassConstantFetchRule($this->getService('0118'));
	}


	public function createService0823(): PHPStan\Rules\Constants\OverridingConstantRule
	{
		return new PHPStan\Rules\Constants\OverridingConstantRule($this->getParameter('checkPhpDocMethodSignatures'));
	}


	public function createService0824(): PHPStan\Rules\Constants\FinalConstantRule
	{
		return new PHPStan\Rules\Constants\FinalConstantRule;
	}


	public function createService0825(): PHPStan\Rules\Constants\ConstantAttributesRule
	{
		return new PHPStan\Rules\Constants\ConstantAttributesRule($this->getService('0167'));
	}


	public function createService0826(): PHPStan\Rules\Constants\ConstantRule
	{
		return new PHPStan\Rules\Constants\ConstantRule($this->getParameter('tips')['discoveringSymbols']);
	}


	public function createService0827(): PHPStan\Rules\Constants\NativeTypedClassConstantRule
	{
		return new PHPStan\Rules\Constants\NativeTypedClassConstantRule;
	}


	public function createService0828(): PHPStan\Rules\Constants\ClassAsClassConstantRule
	{
		return new PHPStan\Rules\Constants\ClassAsClassConstantRule;
	}


	public function createService0829(): PHPStan\Rules\Constants\MagicConstantContextRule
	{
		return new PHPStan\Rules\Constants\MagicConstantContextRule;
	}


	public function createService0830(): PHPStan\Rules\Operators\InvalidIncDecOperationRule
	{
		return new PHPStan\Rules\Operators\InvalidIncDecOperationRule($this->getService('0118'), $this->getService('03'));
	}


	public function createService0831(): PHPStan\Rules\Operators\InvalidUnaryOperationRule
	{
		return new PHPStan\Rules\Operators\InvalidUnaryOperationRule($this->getService('0118'));
	}


	public function createService0832(): PHPStan\Rules\Operators\PipeOperatorRule
	{
		return new PHPStan\Rules\Operators\PipeOperatorRule($this->getService('0118'));
	}


	public function createService0833(): PHPStan\Rules\Operators\BacktickRule
	{
		return new PHPStan\Rules\Operators\BacktickRule($this->getService('03'));
	}


	public function createService0834(): PHPStan\Rules\Operators\InvalidAssignVarRule
	{
		return new PHPStan\Rules\Operators\InvalidAssignVarRule($this->getService('0126'));
	}


	public function createService0835(): PHPStan\Rules\Operators\InvalidComparisonOperationRule
	{
		return new PHPStan\Rules\Operators\InvalidComparisonOperationRule(
			$this->getService('0118'),
			$this->getService('0554'),
			$this->getParameter('featureToggles')['checkExtensionsForComparisonOperators']
		);
	}


	public function createService0836(): PHPStan\Rules\Operators\InvalidBinaryOperationRule
	{
		return new PHPStan\Rules\Operators\InvalidBinaryOperationRule($this->getService('081'), $this->getService('0118'));
	}


	public function createService0837(): PHPStan\Rules\Methods\ConsistentConstructorDeclarationRule
	{
		return new PHPStan\Rules\Methods\ConsistentConstructorDeclarationRule;
	}


	public function createService0838(): PHPStan\Rules\Methods\CallPrivateMethodThroughStaticRule
	{
		return new PHPStan\Rules\Methods\CallPrivateMethodThroughStaticRule;
	}


	public function createService0839(): PHPStan\Rules\Methods\FinalPrivateMethodRule
	{
		return new PHPStan\Rules\Methods\FinalPrivateMethodRule;
	}


	public function createService0840(): PHPStan\Rules\Methods\MethodCallableRule
	{
		return new PHPStan\Rules\Methods\MethodCallableRule($this->getService('0169'));
	}


	public function createService0841(): PHPStan\Rules\Methods\CallToMethodStatementWithNoDiscardRule
	{
		return new PHPStan\Rules\Methods\CallToMethodStatementWithNoDiscardRule($this->getService('0118'));
	}


	public function createService0842(): PHPStan\Rules\Methods\OverridingMethodRule
	{
		return new PHPStan\Rules\Methods\OverridingMethodRule(
			$this->getService('03'),
			$this->getService('0173'),
			$this->getParameter('checkPhpDocMethodSignatures'),
			$this->getService('0174'),
			$this->getService('0170'),
			$this->getService('0172'),
			$this->getParameter('checkMissingOverrideMethodAttribute')
		);
	}


	public function createService0843(): PHPStan\Rules\Methods\CallToConstructorStatementWithoutSideEffectsRule
	{
		return new PHPStan\Rules\Methods\CallToConstructorStatementWithoutSideEffectsRule($this->getService('reflectionProvider'));
	}


	public function createService0844(): PHPStan\Rules\Methods\StaticMethodCallableRule
	{
		return new PHPStan\Rules\Methods\StaticMethodCallableRule($this->getService('0171'));
	}


	public function createService0845(): PHPStan\Rules\Methods\MethodVisibilityInInterfaceRule
	{
		return new PHPStan\Rules\Methods\MethodVisibilityInInterfaceRule;
	}


	public function createService0846(): PHPStan\Rules\Methods\IncompatibleDefaultParameterTypeRule
	{
		return new PHPStan\Rules\Methods\IncompatibleDefaultParameterTypeRule;
	}


	public function createService0847(): PHPStan\Rules\Methods\CallToMethodStatementWithoutSideEffectsRule
	{
		return new PHPStan\Rules\Methods\CallToMethodStatementWithoutSideEffectsRule($this->getService('0118'));
	}


	public function createService0848(): PHPStan\Rules\Methods\ReturnTypeRule
	{
		return new PHPStan\Rules\Methods\ReturnTypeRule($this->getService('0175'));
	}


	public function createService0849(): PHPStan\Rules\Methods\CallStaticMethodsRule
	{
		return new PHPStan\Rules\Methods\CallStaticMethodsRule(
			$this->getService('0171'),
			$this->getService('0146'),
			$this->getService('0123')
		);
	}


	public function createService0850(): PHPStan\Rules\Methods\ExistingClassesInTypehintsRule
	{
		return new PHPStan\Rules\Methods\ExistingClassesInTypehintsRule($this->getService('0183'));
	}


	public function createService0851(): PHPStan\Rules\Methods\MissingMethodImplementationRule
	{
		return new PHPStan\Rules\Methods\MissingMethodImplementationRule;
	}


	public function createService0852(): PHPStan\Rules\Methods\NullsafeMethodCallRule
	{
		return new PHPStan\Rules\Methods\NullsafeMethodCallRule(
			$this->getParameter('treatPhpDocTypesAsCertain'),
			$this->getParameter('tips')['treatPhpDocTypesAsCertain']
		);
	}


	public function createService0853(): PHPStan\Rules\Methods\AbstractPrivateMethodRule
	{
		return new PHPStan\Rules\Methods\AbstractPrivateMethodRule;
	}


	public function createService0854(): PHPStan\Rules\Methods\AbstractMethodInNonAbstractClassRule
	{
		return new PHPStan\Rules\Methods\AbstractMethodInNonAbstractClassRule;
	}


	public function createService0855(): PHPStan\Rules\Methods\ConsistentConstructorRule
	{
		return new PHPStan\Rules\Methods\ConsistentConstructorRule(
			$this->getService('0145'),
			$this->getService('0174'),
			$this->getService('0170')
		);
	}


	public function createService0856(): PHPStan\Rules\Methods\ConstructorReturnTypeRule
	{
		return new PHPStan\Rules\Methods\ConstructorReturnTypeRule;
	}


	public function createService0857(): PHPStan\Rules\Methods\CallMethodsRule
	{
		return new PHPStan\Rules\Methods\CallMethodsRule(
			$this->getService('0169'),
			$this->getService('0146'),
			$this->getService('0123')
		);
	}


	public function createService0858(): PHPStan\Rules\Methods\CallToStaticMethodStatementWithNoDiscardRule
	{
		return new PHPStan\Rules\Methods\CallToStaticMethodStatementWithNoDiscardRule(
			$this->getService('0118'),
			$this->getService('reflectionProvider')
		);
	}


	public function createService0859(): PHPStan\Rules\Methods\MethodAttributesRule
	{
		return new PHPStan\Rules\Methods\MethodAttributesRule($this->getService('0167'));
	}


	public function createService0860(): PHPStan\Rules\Methods\CallToStaticMethodStatementWithoutSideEffectsRule
	{
		return new PHPStan\Rules\Methods\CallToStaticMethodStatementWithoutSideEffectsRule(
			$this->getService('0118'),
			$this->getService('reflectionProvider')
		);
	}


	public function createService0861(): PHPStan\Rules\Methods\MethodCallWithPossiblyRenamedNamedArgumentRule
	{
		return new PHPStan\Rules\Methods\MethodCallWithPossiblyRenamedNamedArgumentRule;
	}


	public function createService0862(): PHPStan\Rules\Methods\MissingMagicSerializationMethodsRule
	{
		return new PHPStan\Rules\Methods\MissingMagicSerializationMethodsRule($this->getService('03'));
	}


	public function createService0863(): PHPStan\Rules\Generics\ClassTemplateTypeRule
	{
		return new PHPStan\Rules\Generics\ClassTemplateTypeRule($this->getService('0178'));
	}


	public function createService0864(): PHPStan\Rules\Generics\MethodTemplateTypeRule
	{
		return new PHPStan\Rules\Generics\MethodTemplateTypeRule($this->getService('0556'), $this->getService('0178'));
	}


	public function createService0865(): PHPStan\Rules\Generics\FunctionTemplateTypeRule
	{
		return new PHPStan\Rules\Generics\FunctionTemplateTypeRule($this->getService('0556'), $this->getService('0178'));
	}


	public function createService0866(): PHPStan\Rules\Generics\MethodSignatureVarianceRule
	{
		return new PHPStan\Rules\Generics\MethodSignatureVarianceRule($this->getService('0181'));
	}


	public function createService0867(): PHPStan\Rules\Generics\UsedTraitsRule
	{
		return new PHPStan\Rules\Generics\UsedTraitsRule($this->getService('0556'), $this->getService('0176'));
	}


	public function createService0868(): PHPStan\Rules\Generics\FunctionSignatureVarianceRule
	{
		return new PHPStan\Rules\Generics\FunctionSignatureVarianceRule($this->getService('0181'));
	}


	public function createService0869(): PHPStan\Rules\Generics\PropertyVarianceRule
	{
		return new PHPStan\Rules\Generics\PropertyVarianceRule($this->getService('0181'));
	}


	public function createService0870(): PHPStan\Rules\Generics\EnumAncestorsRule
	{
		return new PHPStan\Rules\Generics\EnumAncestorsRule($this->getService('0176'), $this->getService('0180'));
	}


	public function createService0871(): PHPStan\Rules\Generics\MethodTagTemplateTypeRule
	{
		return new PHPStan\Rules\Generics\MethodTagTemplateTypeRule($this->getService('0179'));
	}


	public function createService0872(): PHPStan\Rules\Generics\InterfaceAncestorsRule
	{
		return new PHPStan\Rules\Generics\InterfaceAncestorsRule($this->getService('0176'), $this->getService('0180'));
	}


	public function createService0873(): PHPStan\Rules\Generics\TraitTemplateTypeRule
	{
		return new PHPStan\Rules\Generics\TraitTemplateTypeRule($this->getService('0556'), $this->getService('0178'));
	}


	public function createService0874(): PHPStan\Rules\Generics\ClassAncestorsRule
	{
		return new PHPStan\Rules\Generics\ClassAncestorsRule($this->getService('0176'), $this->getService('0180'));
	}


	public function createService0875(): PHPStan\Rules\Generics\MethodTagTemplateTypeTraitRule
	{
		return new PHPStan\Rules\Generics\MethodTagTemplateTypeTraitRule(
			$this->getService('0179'),
			$this->getService('reflectionProvider')
		);
	}


	public function createService0876(): PHPStan\Rules\Generics\InterfaceTemplateTypeRule
	{
		return new PHPStan\Rules\Generics\InterfaceTemplateTypeRule($this->getService('0178'));
	}


	public function createService0877(): PHPStan\Rules\Generics\EnumTemplateTypeRule
	{
		return new PHPStan\Rules\Generics\EnumTemplateTypeRule;
	}


	public function createService0878(): PHPStan\Rules\Traits\TraitUseCollector
	{
		return new PHPStan\Rules\Traits\TraitUseCollector;
	}


	public function createService0879(): PHPStan\Rules\Traits\TraitDeclarationCollector
	{
		return new PHPStan\Rules\Traits\TraitDeclarationCollector;
	}


	public function createService0880(): PHPStan\Rules\DeadCode\PossiblyPureFuncCallCollector
	{
		return new PHPStan\Rules\DeadCode\PossiblyPureFuncCallCollector($this->getService('reflectionProvider'));
	}


	public function createService0881(): PHPStan\Rules\DeadCode\ConstructorWithoutImpurePointsCollector
	{
		return new PHPStan\Rules\DeadCode\ConstructorWithoutImpurePointsCollector($this->getService('0151'));
	}


	public function createService0882(): PHPStan\Rules\DeadCode\PossiblyPureStaticCallCollector
	{
		return new PHPStan\Rules\DeadCode\PossiblyPureStaticCallCollector;
	}


	public function createService0883(): PHPStan\Rules\DeadCode\FunctionWithoutImpurePointsCollector
	{
		return new PHPStan\Rules\DeadCode\FunctionWithoutImpurePointsCollector($this->getService('0151'));
	}


	public function createService0884(): PHPStan\Rules\DeadCode\PossiblyPureMethodCallCollector
	{
		return new PHPStan\Rules\DeadCode\PossiblyPureMethodCallCollector;
	}


	public function createService0885(): PHPStan\Rules\DeadCode\MethodWithoutImpurePointsCollector
	{
		return new PHPStan\Rules\DeadCode\MethodWithoutImpurePointsCollector($this->getService('0151'));
	}


	public function createService0886(): PHPStan\Rules\DeadCode\PossiblyPureNewCollector
	{
		return new PHPStan\Rules\DeadCode\PossiblyPureNewCollector($this->getService('reflectionProvider'));
	}


	public function createService0887(): PhpParser\BuilderFactory
	{
		return new PhpParser\BuilderFactory;
	}


	public function createService0888(): PhpParser\NodeVisitor\NameResolver
	{
		return new PhpParser\NodeVisitor\NameResolver(options: ['preserveOriginalNames' => true]);
	}


	public function createService0889(): PHPStan\PhpDocParser\ParserConfig
	{
		return new PHPStan\PhpDocParser\ParserConfig(['lines' => true]);
	}


	public function createService0890(): PHPStan\PhpDocParser\Lexer\Lexer
	{
		return new PHPStan\PhpDocParser\Lexer\Lexer($this->getService('0889'));
	}


	public function createService0891(): PHPStan\PhpDocParser\Parser\TypeParser
	{
		return new PHPStan\PhpDocParser\Parser\TypeParser($this->getService('0889'), $this->getService('0892'));
	}


	public function createService0892(): PHPStan\PhpDocParser\Parser\ConstExprParser
	{
		return new PHPStan\PhpDocParser\Parser\ConstExprParser($this->getService('0889'));
	}


	public function createService0893(): PHPStan\PhpDocParser\Parser\PhpDocParser
	{
		return new PHPStan\PhpDocParser\Parser\PhpDocParser(
			$this->getService('0889'),
			$this->getService('0891'),
			$this->getService('0892')
		);
	}


	public function createService0894(): PHPStan\PhpDocParser\Printer\Printer
	{
		return new PHPStan\PhpDocParser\Printer\Printer;
	}


	public function createService0895(): PHPStan\BetterReflection\SourceLocator\SourceStubber\PhpStormStubsSourceStubber
	{
		return $this->getService('067')->create();
	}


	public function createService0896(): PHPStan\BetterReflection\SourceLocator\SourceStubber\ReflectionSourceStubber
	{
		return $this->getService('068')->create();
	}


	public function createService0897(): PHPStan\Type\Php\ReflectionGetAttributesMethodReturnTypeExtension
	{
		return new PHPStan\Type\Php\ReflectionGetAttributesMethodReturnTypeExtension('ReflectionClass');
	}


	public function createService0898(): PHPStan\Type\Php\ReflectionGetAttributesMethodReturnTypeExtension
	{
		return new PHPStan\Type\Php\ReflectionGetAttributesMethodReturnTypeExtension('ReflectionClassConstant');
	}


	public function createService0899(): PHPStan\Type\Php\ReflectionGetAttributesMethodReturnTypeExtension
	{
		return new PHPStan\Type\Php\ReflectionGetAttributesMethodReturnTypeExtension('ReflectionFunctionAbstract');
	}


	public function createService0900(): PHPStan\Type\Php\ReflectionGetAttributesMethodReturnTypeExtension
	{
		return new PHPStan\Type\Php\ReflectionGetAttributesMethodReturnTypeExtension('ReflectionParameter');
	}


	public function createService0901(): PHPStan\Type\Php\ReflectionGetAttributesMethodReturnTypeExtension
	{
		return new PHPStan\Type\Php\ReflectionGetAttributesMethodReturnTypeExtension('ReflectionProperty');
	}


	public function createService0902(): PHPStan\Type\Php\DateTimeModifyReturnTypeExtension
	{
		return new PHPStan\Type\Php\DateTimeModifyReturnTypeExtension('DateTime');
	}


	public function createService0903(): PHPStan\Type\Php\DateTimeModifyReturnTypeExtension
	{
		return new PHPStan\Type\Php\DateTimeModifyReturnTypeExtension('DateTimeImmutable');
	}


	public function createService0904(): PHPStan\Reflection\PHPStan\NativeReflectionEnumReturnDynamicReturnTypeExtension
	{
		return new PHPStan\Reflection\PHPStan\NativeReflectionEnumReturnDynamicReturnTypeExtension(
			'PHPStan\Reflection\ClassReflection',
			'getNativeReflection'
		);
	}


	public function createService0905(): PHPStan\Reflection\PHPStan\NativeReflectionEnumReturnDynamicReturnTypeExtension
	{
		return new PHPStan\Reflection\PHPStan\NativeReflectionEnumReturnDynamicReturnTypeExtension(
			'PHPStan\Reflection\Php\BuiltinMethodReflection',
			'getDeclaringClass'
		);
	}


	public function createService0906(): PHPStan\Reflection\BetterReflection\Type\AdapterReflectionEnumCaseDynamicReturnTypeExtension
	{
		return new PHPStan\Reflection\BetterReflection\Type\AdapterReflectionEnumCaseDynamicReturnTypeExtension('PHPStan\BetterReflection\Reflection\Adapter\ReflectionEnumBackedCase');
	}


	public function createService0907(): PHPStan\Reflection\BetterReflection\Type\AdapterReflectionEnumCaseDynamicReturnTypeExtension
	{
		return new PHPStan\Reflection\BetterReflection\Type\AdapterReflectionEnumCaseDynamicReturnTypeExtension('PHPStan\BetterReflection\Reflection\Adapter\ReflectionEnumUnitCase');
	}


	public function createService0908(): PHPStan\Rules\Exceptions\MissingCheckedExceptionInFunctionThrowsRule
	{
		return new PHPStan\Rules\Exceptions\MissingCheckedExceptionInFunctionThrowsRule($this->getService('0120'));
	}


	public function createService0909(): PHPStan\Rules\Exceptions\MissingCheckedExceptionInMethodThrowsRule
	{
		return new PHPStan\Rules\Exceptions\MissingCheckedExceptionInMethodThrowsRule($this->getService('0120'));
	}


	public function createService0910(): PHPStan\Rules\Exceptions\MissingCheckedExceptionInPropertyHookThrowsRule
	{
		return new PHPStan\Rules\Exceptions\MissingCheckedExceptionInPropertyHookThrowsRule($this->getService('0120'));
	}


	public function createService0911(): PHPStan\Rules\Properties\UninitializedPropertyRule
	{
		return new PHPStan\Rules\Properties\UninitializedPropertyRule($this->getService('049'));
	}


	public function createService0912(): PHPStan\Rules\Exceptions\MethodThrowTypeCovarianceRule
	{
		return new PHPStan\Rules\Exceptions\MethodThrowTypeCovarianceRule($this->getService('0168'), true);
	}


	public function createService0913(): PHPStan\Rules\Classes\NewStaticInAbstractClassStaticMethodRule
	{
		return new PHPStan\Rules\Classes\NewStaticInAbstractClassStaticMethodRule;
	}


	public function createService0914(): PHPStan\Rules\InternalTag\RestrictedInternalClassConstantUsageExtension
	{
		return new PHPStan\Rules\InternalTag\RestrictedInternalClassConstantUsageExtension($this->getService('0109'));
	}


	public function createService0915(): PHPStan\Rules\InternalTag\RestrictedInternalClassNameUsageExtension
	{
		return new PHPStan\Rules\InternalTag\RestrictedInternalClassNameUsageExtension($this->getService('0109'));
	}


	public function createService0916(): PHPStan\Rules\InternalTag\RestrictedInternalFunctionUsageExtension
	{
		return new PHPStan\Rules\InternalTag\RestrictedInternalFunctionUsageExtension($this->getService('0109'));
	}


	public function createService0917(): PHPStan\Rules\Variables\AssignToByRefExprFromForeachRule
	{
		return new PHPStan\Rules\Variables\AssignToByRefExprFromForeachRule($this->getService('081'));
	}


	public function createService0918(): PHPStan\Rules\InternalTag\RestrictedInternalPropertyUsageExtension
	{
		return new PHPStan\Rules\InternalTag\RestrictedInternalPropertyUsageExtension($this->getService('0109'));
	}


	public function createService0919(): PHPStan\Rules\InternalTag\RestrictedInternalMethodUsageExtension
	{
		return new PHPStan\Rules\InternalTag\RestrictedInternalMethodUsageExtension($this->getService('0109'));
	}


	public function createService0920(): PHPStan\Rules\Constants\ValueAssignedToDefineRule
	{
		return new PHPStan\Rules\Constants\ValueAssignedToDefineRule($this->getService('0187'));
	}


	public function createService0921(): PHPStan\Rules\Constants\ValueAssignedToGlobalConstantRule
	{
		return new PHPStan\Rules\Constants\ValueAssignedToGlobalConstantRule($this->getService('0187'));
	}


	public function createService0922(): PHPStan\Rules\Exceptions\TooWideFunctionThrowTypeRule
	{
		return new PHPStan\Rules\Exceptions\TooWideFunctionThrowTypeRule($this->getService('0121'));
	}


	public function createService0923(): PHPStan\Rules\Exceptions\TooWideMethodThrowTypeRule
	{
		return new PHPStan\Rules\Exceptions\TooWideMethodThrowTypeRule(
			$this->getService('0556'),
			$this->getService('0121'),
			false,
			false
		);
	}


	public function createService0924(): PHPStan\Rules\Exceptions\TooWidePropertyHookThrowTypeRule
	{
		return new PHPStan\Rules\Exceptions\TooWidePropertyHookThrowTypeRule($this->getService('0121'), false);
	}


	public function createService0925(): PHPStan\Rules\Keywords\UnusedLabelRule
	{
		return new PHPStan\Rules\Keywords\UnusedLabelRule;
	}


	public function createService0926(): PHPStan\Rules\Comparison\ImpossibleInArrayHaystackFiniteTypesRule
	{
		return new PHPStan\Rules\Comparison\ImpossibleInArrayHaystackFiniteTypesRule($this->getService('072'), true);
	}


	public function createService0927(): PHPStan\Rules\Comparison\SwitchConditionRule
	{
		return new PHPStan\Rules\Comparison\SwitchConditionRule(
			$this->getService('0129'),
			$this->getService('0127'),
			$this->getService('081'),
			$this->getService('03'),
			true
		);
	}


	public function createService0928(): PHPStan\Rules\DeadCode\UnusedVariableRule
	{
		return new PHPStan\Rules\DeadCode\UnusedVariableRule($this->getService('081'));
	}


	public function createService0929(): PHPStan\Rules\Functions\UnusedFunctionParametersRule
	{
		return new PHPStan\Rules\Functions\UnusedFunctionParametersRule($this->getService('0152'));
	}


	public function createService0930(): PHPStan\Rules\Methods\UnusedMethodParametersRule
	{
		return new PHPStan\Rules\Methods\UnusedMethodParametersRule($this->getService('0152'));
	}


	public function createService0931(): PHPStan\Rules\Functions\ParameterCastableToNumberRule
	{
		return new PHPStan\Rules\Functions\ParameterCastableToNumberRule(
			$this->getService('reflectionProvider'),
			$this->getService('0148'),
			$this->getService('03')
		);
	}


	public function createService0932(): PHPStan\Rules\Functions\PrintfParameterTypeRule
	{
		return new PHPStan\Rules\Functions\PrintfParameterTypeRule(
			$this->getService('0352'),
			$this->getService('reflectionProvider'),
			$this->getService('0118'),
			false
		);
	}


	public function createService0933(): PHPStan\Rules\DateIntervalInstantiationRule
	{
		return new PHPStan\Rules\DateIntervalInstantiationRule;
	}


	public function createService0934(): PHPStan\Rules\Functions\SortWithoutEffectRule
	{
		return new PHPStan\Rules\Functions\SortWithoutEffectRule($this->getService('reflectionProvider'), true, true);
	}


	public function createService0935(): Larastan\Larastan\Methods\RelationForwardsCallsExtension
	{
		return new Larastan\Larastan\Methods\RelationForwardsCallsExtension(
			$this->getService('01018'),
			$this->getService('reflectionProvider')
		);
	}


	public function createService0936(): Larastan\Larastan\Methods\ModelForwardsCallsExtension
	{
		return new Larastan\Larastan\Methods\ModelForwardsCallsExtension(
			$this->getService('01018'),
			$this->getService('reflectionProvider'),
			$this->getService('0937')
		);
	}


	public function createService0937(): Larastan\Larastan\Methods\EloquentBuilderForwardsCallsExtension
	{
		return new Larastan\Larastan\Methods\EloquentBuilderForwardsCallsExtension(
			$this->getService('01018'),
			$this->getService('reflectionProvider')
		);
	}


	public function createService0938(): Larastan\Larastan\Methods\HigherOrderTapProxyExtension
	{
		return new Larastan\Larastan\Methods\HigherOrderTapProxyExtension;
	}


	public function createService0939(): Larastan\Larastan\Methods\HigherOrderCollectionProxyExtension
	{
		return new Larastan\Larastan\Methods\HigherOrderCollectionProxyExtension($this->getService('01055'));
	}


	public function createService0940(): Larastan\Larastan\Methods\StorageMethodsClassReflectionExtension
	{
		return new Larastan\Larastan\Methods\StorageMethodsClassReflectionExtension($this->getService('reflectionProvider'));
	}


	public function createService0941(): Larastan\Larastan\Methods\ContractsMethodsExtension
	{
		return new Larastan\Larastan\Methods\ContractsMethodsExtension($this->getService('reflectionProvider'));
	}


	public function createService0942(): Larastan\Larastan\Methods\FacadesMethodsExtension
	{
		return new Larastan\Larastan\Methods\FacadesMethodsExtension($this->getService('reflectionProvider'));
	}


	public function createService0943(): Larastan\Larastan\Methods\ManagersMethodsExtension
	{
		return new Larastan\Larastan\Methods\ManagersMethodsExtension($this->getService('reflectionProvider'));
	}


	public function createService0944(): Larastan\Larastan\Methods\AuthsMethodsExtension
	{
		return new Larastan\Larastan\Methods\AuthsMethodsExtension($this->getService('reflectionProvider'));
	}


	public function createService0945(): Larastan\Larastan\Methods\ModelFactoryMethodsClassReflectionExtension
	{
		return new Larastan\Larastan\Methods\ModelFactoryMethodsClassReflectionExtension($this->getService('reflectionProvider'));
	}


	public function createService0946(): Larastan\Larastan\Methods\RedirectResponseMethodsClassReflectionExtension
	{
		return new Larastan\Larastan\Methods\RedirectResponseMethodsClassReflectionExtension;
	}


	public function createService0947(): Larastan\Larastan\Methods\MacroMethodsClassReflectionExtension
	{
		return new Larastan\Larastan\Methods\MacroMethodsClassReflectionExtension(
			$this->getService('reflectionProvider'),
			$this->getService('0561')
		);
	}


	public function createService0948(): Larastan\Larastan\Methods\ViewWithMethodsClassReflectionExtension
	{
		return new Larastan\Larastan\Methods\ViewWithMethodsClassReflectionExtension;
	}


	public function createService0949(): Larastan\Larastan\Properties\ModelAccessorExtension
	{
		return new Larastan\Larastan\Properties\ModelAccessorExtension($this->getService('01016'));
	}


	public function createService0950(): Larastan\Larastan\Properties\ModelPropertyExtension
	{
		return new Larastan\Larastan\Properties\ModelPropertyExtension($this->getService('01016'));
	}


	public function createService0951(): Larastan\Larastan\Properties\HigherOrderCollectionProxyPropertyExtension
	{
		return new Larastan\Larastan\Properties\HigherOrderCollectionProxyPropertyExtension($this->getService('01055'));
	}


	public function createService0952(): Larastan\Larastan\ReturnTypes\HigherOrderTapProxyExtension
	{
		return new Larastan\Larastan\ReturnTypes\HigherOrderTapProxyExtension;
	}


	public function createService0953(): Larastan\Larastan\ReturnTypes\ContainerArrayAccessDynamicMethodReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\ContainerArrayAccessDynamicMethodReturnTypeExtension('Illuminate\Contracts\Container\Container');
	}


	public function createService0954(): Larastan\Larastan\ReturnTypes\ContainerArrayAccessDynamicMethodReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\ContainerArrayAccessDynamicMethodReturnTypeExtension('Illuminate\Container\Container');
	}


	public function createService0955(): Larastan\Larastan\ReturnTypes\ContainerArrayAccessDynamicMethodReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\ContainerArrayAccessDynamicMethodReturnTypeExtension('Illuminate\Foundation\Application');
	}


	public function createService0956(): Larastan\Larastan\ReturnTypes\ContainerArrayAccessDynamicMethodReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\ContainerArrayAccessDynamicMethodReturnTypeExtension('Illuminate\Contracts\Foundation\Application');
	}


	public function createService0957(): Larastan\Larastan\Properties\ModelRelationsExtension
	{
		return new Larastan\Larastan\Properties\ModelRelationsExtension($this->getService('0974'));
	}


	public function createService0958(): Larastan\Larastan\ReturnTypes\ModelSerializationDynamicMethodReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\ModelSerializationDynamicMethodReturnTypeExtension(
			$this->getService('01016'),
			$this->getService('01014'),
			$this->getService('reflectionProvider')
		);
	}


	public function createService0959(): Larastan\Larastan\ReturnTypes\ModelOnlyDynamicMethodReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\ModelOnlyDynamicMethodReturnTypeExtension;
	}


	public function createService0960(): Larastan\Larastan\ReturnTypes\ModelFactoryDynamicStaticMethodReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\ModelFactoryDynamicStaticMethodReturnTypeExtension($this->getService('reflectionProvider'));
	}


	public function createService0961(): Larastan\Larastan\ReturnTypes\ModelDynamicStaticMethodReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\ModelDynamicStaticMethodReturnTypeExtension(
			$this->getService('01018'),
			$this->getService('0974'),
			$this->getService('reflectionProvider')
		);
	}


	public function createService0962(): Larastan\Larastan\ReturnTypes\AppMakeDynamicReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\AppMakeDynamicReturnTypeExtension($this->getService('01052'));
	}


	public function createService0963(): Larastan\Larastan\ReturnTypes\AuthExtension
	{
		return new Larastan\Larastan\ReturnTypes\AuthExtension;
	}


	public function createService0964(): Larastan\Larastan\ReturnTypes\GuardDynamicStaticMethodReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\GuardDynamicStaticMethodReturnTypeExtension;
	}


	public function createService0965(): Larastan\Larastan\ReturnTypes\AuthManagerExtension
	{
		return new Larastan\Larastan\ReturnTypes\AuthManagerExtension;
	}


	public function createService0966(): Larastan\Larastan\ReturnTypes\DateExtension
	{
		return new Larastan\Larastan\ReturnTypes\DateExtension;
	}


	public function createService0967(): Larastan\Larastan\ReturnTypes\GuardExtension
	{
		return new Larastan\Larastan\ReturnTypes\GuardExtension;
	}


	public function createService0968(): Larastan\Larastan\ReturnTypes\RequestFileExtension
	{
		return new Larastan\Larastan\ReturnTypes\RequestFileExtension;
	}


	public function createService0969(): Larastan\Larastan\ReturnTypes\RequestRouteExtension
	{
		return new Larastan\Larastan\ReturnTypes\RequestRouteExtension;
	}


	public function createService0970(): Larastan\Larastan\ReturnTypes\RequestUserExtension
	{
		return new Larastan\Larastan\ReturnTypes\RequestUserExtension;
	}


	public function createService0971(): Larastan\Larastan\ReturnTypes\EloquentBuilderExtension
	{
		return new Larastan\Larastan\ReturnTypes\EloquentBuilderExtension(
			$this->getService('reflectionProvider'),
			$this->getService('0974')
		);
	}


	public function createService0972(): Larastan\Larastan\ReturnTypes\RelationCollectionExtension
	{
		return new Larastan\Larastan\ReturnTypes\RelationCollectionExtension(
			$this->getService('reflectionProvider'),
			$this->getService('0974')
		);
	}


	public function createService0973(): Larastan\Larastan\ReturnTypes\TestCaseExtension
	{
		return new Larastan\Larastan\ReturnTypes\TestCaseExtension;
	}


	public function createService0974(): Larastan\Larastan\Support\CollectionHelper
	{
		return new Larastan\Larastan\Support\CollectionHelper($this->getService('reflectionProvider'));
	}


	public function createService0975(): Larastan\Larastan\ReturnTypes\Helpers\AuthExtension
	{
		return new Larastan\Larastan\ReturnTypes\Helpers\AuthExtension;
	}


	public function createService0976(): Larastan\Larastan\ReturnTypes\Helpers\CollectExtension
	{
		return new Larastan\Larastan\ReturnTypes\Helpers\CollectExtension($this->getService('0974'), false);
	}


	public function createService0977(): Larastan\Larastan\ReturnTypes\Helpers\NowAndTodayExtension
	{
		return new Larastan\Larastan\ReturnTypes\Helpers\NowAndTodayExtension;
	}


	public function createService0978(): Larastan\Larastan\ReturnTypes\Helpers\ResponseExtension
	{
		return new Larastan\Larastan\ReturnTypes\Helpers\ResponseExtension;
	}


	public function createService0979(): Larastan\Larastan\ReturnTypes\Helpers\ValidatorExtension
	{
		return new Larastan\Larastan\ReturnTypes\Helpers\ValidatorExtension;
	}


	public function createService0980(): Larastan\Larastan\ReturnTypes\Helpers\LiteralExtension
	{
		return new Larastan\Larastan\ReturnTypes\Helpers\LiteralExtension;
	}


	public function createService0981(): Larastan\Larastan\ReturnTypes\CollectionFilterRejectDynamicReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\CollectionFilterRejectDynamicReturnTypeExtension;
	}


	public function createService0982(): Larastan\Larastan\ReturnTypes\CollectionWhereNotNullDynamicReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\CollectionWhereNotNullDynamicReturnTypeExtension;
	}


	public function createService0983(): Larastan\Larastan\ReturnTypes\FactoryDynamicMethodReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\FactoryDynamicMethodReturnTypeExtension;
	}


	public function createService0984(): Larastan\Larastan\Types\AbortIfFunctionTypeSpecifyingExtension
	{
		return new Larastan\Larastan\Types\AbortIfFunctionTypeSpecifyingExtension(false, 'abort');
	}


	public function createService0985(): Larastan\Larastan\Types\AbortIfFunctionTypeSpecifyingExtension
	{
		return new Larastan\Larastan\Types\AbortIfFunctionTypeSpecifyingExtension(true, 'abort');
	}


	public function createService0986(): Larastan\Larastan\Types\AbortIfFunctionTypeSpecifyingExtension
	{
		return new Larastan\Larastan\Types\AbortIfFunctionTypeSpecifyingExtension(false, 'throw');
	}


	public function createService0987(): Larastan\Larastan\Types\AbortIfFunctionTypeSpecifyingExtension
	{
		return new Larastan\Larastan\Types\AbortIfFunctionTypeSpecifyingExtension(true, 'throw');
	}


	public function createService0988(): Larastan\Larastan\ReturnTypes\Helpers\AppExtension
	{
		return new Larastan\Larastan\ReturnTypes\Helpers\AppExtension($this->getService('01052'));
	}


	public function createService0989(): Larastan\Larastan\ReturnTypes\Helpers\ValueExtension
	{
		return new Larastan\Larastan\ReturnTypes\Helpers\ValueExtension;
	}


	public function createService0990(): Larastan\Larastan\ReturnTypes\Helpers\StrExtension
	{
		return new Larastan\Larastan\ReturnTypes\Helpers\StrExtension;
	}


	public function createService0991(): Larastan\Larastan\ReturnTypes\Helpers\TapExtension
	{
		return new Larastan\Larastan\ReturnTypes\Helpers\TapExtension;
	}


	public function createService0992(): Larastan\Larastan\ReturnTypes\StorageDynamicStaticMethodReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\StorageDynamicStaticMethodReturnTypeExtension;
	}


	public function createService0993(): Larastan\Larastan\Types\GenericEloquentCollectionTypeNodeResolverExtension
	{
		return new Larastan\Larastan\Types\GenericEloquentCollectionTypeNodeResolverExtension($this->getService('026'));
	}


	public function createService0994(): Larastan\Larastan\Types\ViewStringTypeNodeResolverExtension
	{
		return new Larastan\Larastan\Types\ViewStringTypeNodeResolverExtension;
	}


	public function createService0995(): Larastan\Larastan\Rules\OctaneCompatibilityRule
	{
		return new Larastan\Larastan\Rules\OctaneCompatibilityRule;
	}


	public function createService0996(): Larastan\Larastan\Rules\NoEnvCallsOutsideOfConfigRule
	{
		return new Larastan\Larastan\Rules\NoEnvCallsOutsideOfConfigRule(
			[],
			$this->getService('09'),
			$this->getParameter('singleReflectionFile'),
			$this->getParameter('singleReflectionInsteadOfFile')
		);
	}


	public function createService0997(): Larastan\Larastan\Rules\NoModelMakeRule
	{
		return new Larastan\Larastan\Rules\NoModelMakeRule($this->getService('reflectionProvider'));
	}


	public function createService0998(): Larastan\Larastan\Rules\NoImplicitQueryBuilderCallRule
	{
		return new Larastan\Larastan\Rules\NoImplicitQueryBuilderCallRule($this->getService('0936'));
	}


	public function createService0999(): Larastan\Larastan\Rules\NoUnnecessaryCollectionCallRule
	{
		return new Larastan\Larastan\Rules\NoUnnecessaryCollectionCallRule(
			$this->getService('reflectionProvider'),
			$this->getService('0950'),
			[],
			[]
		);
	}


	public function createService01000(): Larastan\Larastan\Rules\NoUnnecessaryEnumerableToArrayCallsRule
	{
		return new Larastan\Larastan\Rules\NoUnnecessaryEnumerableToArrayCallsRule;
	}


	public function createService01001(): Larastan\Larastan\Rules\ModelAppendsRule
	{
		return new Larastan\Larastan\Rules\ModelAppendsRule($this->getService('01016'));
	}


	public function createService01002(): Larastan\Larastan\Rules\NoPublicModelScopeAndAccessorRule
	{
		return new Larastan\Larastan\Rules\NoPublicModelScopeAndAccessorRule;
	}


	public function createService01003(): Larastan\Larastan\Types\GenericEloquentBuilderTypeNodeResolverExtension
	{
		return new Larastan\Larastan\Types\GenericEloquentBuilderTypeNodeResolverExtension($this->getService('reflectionProvider'));
	}


	public function createService01004(): Larastan\Larastan\ReturnTypes\AppEnvironmentReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\AppEnvironmentReturnTypeExtension('Illuminate\Foundation\Application');
	}


	public function createService01005(): Larastan\Larastan\ReturnTypes\AppEnvironmentReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\AppEnvironmentReturnTypeExtension('Illuminate\Contracts\Foundation\Application');
	}


	public function createService01006(): Larastan\Larastan\ReturnTypes\AppFacadeEnvironmentReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\AppFacadeEnvironmentReturnTypeExtension;
	}


	public function createService01007(): Larastan\Larastan\Types\ModelProperty\ModelPropertyTypeNodeResolverExtension
	{
		return new Larastan\Larastan\Types\ModelProperty\ModelPropertyTypeNodeResolverExtension(
			$this->getService('026'),
			false,
			$this->getService('01016')
		);
	}


	public function createService01008(): Larastan\Larastan\Types\CollectionOf\CollectionOfTypeNodeResolverExtension
	{
		return new Larastan\Larastan\Types\CollectionOf\CollectionOfTypeNodeResolverExtension($this->getService('0974'));
	}


	public function createService01009(): Larastan\Larastan\ClosureTypes\RelationshipQueryCallbackExtension
	{
		return new Larastan\Larastan\ClosureTypes\RelationshipQueryCallbackExtension($this->getService('01018'));
	}


	public function createService01010(): Larastan\Larastan\Types\BuilderOf\BuilderOfTypeNodeResolverExtension
	{
		return new Larastan\Larastan\Types\BuilderOf\BuilderOfTypeNodeResolverExtension($this->getService('01018'));
	}


	public function createService01011(): Larastan\Larastan\Types\RelationOf\RelationOfTypeNodeResolverExtension
	{
		return new Larastan\Larastan\Types\RelationOf\RelationOfTypeNodeResolverExtension;
	}


	public function createService01012(): Larastan\Larastan\Properties\MigrationHelper
	{
		return new Larastan\Larastan\Properties\MigrationHelper(
			$this->getService('migrationsParser'),
			[],
			$this->getService('09'),
			false,
			$this->getService('reflectionProvider'),
			$this->getService('072'),
			$this->getService('sqlParser')
		);
	}


	public function createService01013(): Larastan\Larastan\Properties\SquashedMigrationHelper
	{
		return new Larastan\Larastan\Properties\SquashedMigrationHelper(
			[],
			$this->getService('09'),
			$this->getService('01024'),
			$this->getService('sqlParser'),
			false
		);
	}


	public function createService01014(): Larastan\Larastan\Properties\ModelCastHelper
	{
		return new Larastan\Larastan\Properties\ModelCastHelper(
			$this->getService('reflectionProvider'),
			$this->getService('currentPhpVersionSimpleDirectParser'),
			false,
			$this->getService('0191')
		);
	}


	public function createService01015(): Larastan\Larastan\Properties\MigrationCache
	{
		return new Larastan\Larastan\Properties\MigrationCache(
			'/home/runner/work/snck-panel/snck-panel/laravel-panel/storage/framework/phpstan',
			true
		);
	}


	public function createService01016(): Larastan\Larastan\Properties\ModelPropertyHelper
	{
		return new Larastan\Larastan\Properties\ModelPropertyHelper(
			$this->getService('025'),
			$this->getService('01012'),
			$this->getService('01013'),
			$this->getService('01014'),
			$this->getService('01015')
		);
	}


	public function createService01017(): Larastan\Larastan\Rules\ModelRuleHelper
	{
		return new Larastan\Larastan\Rules\ModelRuleHelper;
	}


	public function createService01018(): Larastan\Larastan\Methods\BuilderHelper
	{
		return new Larastan\Larastan\Methods\BuilderHelper($this->getService('reflectionProvider'), false, $this->getService('0947'));
	}


	public function createService01019(): Larastan\Larastan\Rules\ModelRelationDefaultsRule
	{
		return new Larastan\Larastan\Rules\ModelRelationDefaultsRule($this->getService('01020'), $this->getService('072'));
	}


	public function createService01020(): Larastan\Larastan\Rules\RelationExistenceHelper
	{
		return new Larastan\Larastan\Rules\RelationExistenceHelper($this->getService('01017'));
	}


	public function createService01021(): Larastan\Larastan\Rules\RelationExistenceRule
	{
		return new Larastan\Larastan\Rules\RelationExistenceRule($this->getService('01020'));
	}


	public function createService01022(): Larastan\Larastan\Rules\CheckDispatchArgumentTypesCompatibleWithClassConstructorRule
	{
		return new Larastan\Larastan\Rules\CheckDispatchArgumentTypesCompatibleWithClassConstructorRule(
			$this->getService('reflectionProvider'),
			$this->getService('0146'),
			'Illuminate\Foundation\Bus\Dispatchable'
		);
	}


	public function createService01023(): Larastan\Larastan\Rules\CheckDispatchArgumentTypesCompatibleWithClassConstructorRule
	{
		return new Larastan\Larastan\Rules\CheckDispatchArgumentTypesCompatibleWithClassConstructorRule(
			$this->getService('reflectionProvider'),
			$this->getService('0146'),
			'Illuminate\Foundation\Events\Dispatchable'
		);
	}


	public function createService01024(): Larastan\Larastan\Properties\Schema\MySqlDataTypeToPhpTypeConverter
	{
		return new Larastan\Larastan\Properties\Schema\MySqlDataTypeToPhpTypeConverter;
	}


	public function createService01025(): Larastan\Larastan\LarastanStubFilesExtension
	{
		return new Larastan\Larastan\LarastanStubFilesExtension;
	}


	public function createService01026(): Larastan\Larastan\Rules\UnusedViewsRule
	{
		return new Larastan\Larastan\Rules\UnusedViewsRule($this->getService('01034'), $this->getService('01035'));
	}


	public function createService01027(): Larastan\Larastan\Collectors\UsedViewFunctionCollector
	{
		return new Larastan\Larastan\Collectors\UsedViewFunctionCollector($this->getService('reflectionProvider'));
	}


	public function createService01028(): Larastan\Larastan\Collectors\UsedEmailViewCollector
	{
		return new Larastan\Larastan\Collectors\UsedEmailViewCollector;
	}


	public function createService01029(): Larastan\Larastan\Collectors\UsedEmailAlternativeSyntaxViewCollector
	{
		return new Larastan\Larastan\Collectors\UsedEmailAlternativeSyntaxViewCollector;
	}


	public function createService01030(): Larastan\Larastan\Collectors\UsedEmailSendViewCollector
	{
		return new Larastan\Larastan\Collectors\UsedEmailSendViewCollector;
	}


	public function createService01031(): Larastan\Larastan\Collectors\UsedViewMakeCollector
	{
		return new Larastan\Larastan\Collectors\UsedViewMakeCollector;
	}


	public function createService01032(): Larastan\Larastan\Collectors\UsedViewFacadeMakeCollector
	{
		return new Larastan\Larastan\Collectors\UsedViewFacadeMakeCollector;
	}


	public function createService01033(): Larastan\Larastan\Collectors\UsedRouteFacadeViewCollector
	{
		return new Larastan\Larastan\Collectors\UsedRouteFacadeViewCollector;
	}


	public function createService01034(): Larastan\Larastan\Collectors\UsedViewInAnotherViewCollector
	{
		return new Larastan\Larastan\Collectors\UsedViewInAnotherViewCollector($this->getService('01036'), $this->getService('01035'));
	}


	public function createService01035(): Larastan\Larastan\Support\ViewFileHelper
	{
		return new Larastan\Larastan\Support\ViewFileHelper([], $this->getService('09'));
	}


	public function createService01036(): Larastan\Larastan\Support\ViewParser
	{
		return new Larastan\Larastan\Support\ViewParser($this->getService('currentPhpVersionSimpleDirectParser'));
	}


	public function createService01037(): Larastan\Larastan\Rules\NoMissingTranslationsRule
	{
		return new Larastan\Larastan\Rules\NoMissingTranslationsRule($this->getService('01041'), $this->getService('01074'), []);
	}


	public function createService01038(): Larastan\Larastan\Collectors\UsedTranslationFunctionCollector
	{
		return new Larastan\Larastan\Collectors\UsedTranslationFunctionCollector;
	}


	public function createService01039(): Larastan\Larastan\Collectors\UsedTranslationTranslatorCollector
	{
		return new Larastan\Larastan\Collectors\UsedTranslationTranslatorCollector;
	}


	public function createService01040(): Larastan\Larastan\Collectors\UsedTranslationFacadeCollector
	{
		return new Larastan\Larastan\Collectors\UsedTranslationFacadeCollector;
	}


	public function createService01041(): Larastan\Larastan\Collectors\UsedTranslationViewCollector
	{
		return new Larastan\Larastan\Collectors\UsedTranslationViewCollector($this->getService('01036'), $this->getService('01035'));
	}


	public function createService01042(): Larastan\Larastan\ReturnTypes\ApplicationMakeDynamicReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\ApplicationMakeDynamicReturnTypeExtension($this->getService('01052'));
	}


	public function createService01043(): Larastan\Larastan\ReturnTypes\ContainerMakeDynamicReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\ContainerMakeDynamicReturnTypeExtension($this->getService('01052'));
	}


	public function createService01044(): Larastan\Larastan\ReturnTypes\ConsoleCommand\ArgumentDynamicReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\ConsoleCommand\ArgumentDynamicReturnTypeExtension(
			$this->getService('01053'),
			$this->getService('01054')
		);
	}


	public function createService01045(): Larastan\Larastan\ReturnTypes\ConsoleCommand\HasArgumentDynamicReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\ConsoleCommand\HasArgumentDynamicReturnTypeExtension($this->getService('01053'));
	}


	public function createService01046(): Larastan\Larastan\ReturnTypes\ConsoleCommand\OptionDynamicReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\ConsoleCommand\OptionDynamicReturnTypeExtension(
			$this->getService('01053'),
			$this->getService('01054')
		);
	}


	public function createService01047(): Larastan\Larastan\ReturnTypes\ConsoleCommand\HasOptionDynamicReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\ConsoleCommand\HasOptionDynamicReturnTypeExtension($this->getService('01053'));
	}


	public function createService01048(): Larastan\Larastan\ReturnTypes\TranslatorGetReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\TranslatorGetReturnTypeExtension;
	}


	public function createService01049(): Larastan\Larastan\ReturnTypes\LangGetReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\LangGetReturnTypeExtension;
	}


	public function createService01050(): Larastan\Larastan\ReturnTypes\TransHelperReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\TransHelperReturnTypeExtension;
	}


	public function createService01051(): Larastan\Larastan\ReturnTypes\DoubleUnderscoreHelperReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\DoubleUnderscoreHelperReturnTypeExtension;
	}


	public function createService01052(): Larastan\Larastan\ReturnTypes\AppMakeHelper
	{
		return new Larastan\Larastan\ReturnTypes\AppMakeHelper;
	}


	public function createService01053(): Larastan\Larastan\Internal\ConsoleApplicationResolver
	{
		return new Larastan\Larastan\Internal\ConsoleApplicationResolver;
	}


	public function createService01054(): Larastan\Larastan\Internal\ConsoleApplicationHelper
	{
		return new Larastan\Larastan\Internal\ConsoleApplicationHelper($this->getService('01053'));
	}


	public function createService01055(): Larastan\Larastan\Support\HigherOrderCollectionProxyHelper
	{
		return new Larastan\Larastan\Support\HigherOrderCollectionProxyHelper($this->getService('reflectionProvider'));
	}


	public function createService01056(): Larastan\Larastan\ReturnTypes\Helpers\ConfigFunctionDynamicFunctionReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\Helpers\ConfigFunctionDynamicFunctionReturnTypeExtension($this->getService('01060'));
	}


	public function createService01057(): Larastan\Larastan\ReturnTypes\ConfigRepositoryDynamicMethodReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\ConfigRepositoryDynamicMethodReturnTypeExtension($this->getService('01060'));
	}


	public function createService01058(): Larastan\Larastan\ReturnTypes\ConfigFacadeCollectionDynamicStaticMethodReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\ConfigFacadeCollectionDynamicStaticMethodReturnTypeExtension($this->getService('01060'));
	}


	public function createService01059(): Larastan\Larastan\Support\ConfigParser
	{
		return new Larastan\Larastan\Support\ConfigParser(
			$this->getService('09'),
			$this->getService('currentPhpVersionSimpleDirectParser'),
			$this->getService('0556'),
			[],
			true
		);
	}


	public function createService01060(): Larastan\Larastan\Internal\ConfigHelper
	{
		return new Larastan\Larastan\Internal\ConfigHelper($this->getService('01059'));
	}


	public function createService01061(): Larastan\Larastan\ReturnTypes\Helpers\EnvFunctionDynamicFunctionReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\Helpers\EnvFunctionDynamicFunctionReturnTypeExtension;
	}


	public function createService01062(): Larastan\Larastan\ReturnTypes\FormRequestSafeDynamicMethodReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\FormRequestSafeDynamicMethodReturnTypeExtension;
	}


	public function createService01063(): Larastan\Larastan\ReturnTypes\EloquentCollectionMapDynamicReturnTypeExtension
	{
		return new Larastan\Larastan\ReturnTypes\EloquentCollectionMapDynamicReturnTypeExtension;
	}


	public function createService01064(): Larastan\Larastan\Rules\NoAuthFacadeInRequestScopeRule
	{
		return new Larastan\Larastan\Rules\NoAuthFacadeInRequestScopeRule;
	}


	public function createService01065(): Larastan\Larastan\Rules\NoAuthHelperInRequestScopeRule
	{
		return new Larastan\Larastan\Rules\NoAuthHelperInRequestScopeRule;
	}


	public function createService01066(): Larastan\Larastan\Rules\ConfigCollectionRule
	{
		return new Larastan\Larastan\Rules\ConfigCollectionRule($this->getService('01060'));
	}


	public function createService01067(): Larastan\Larastan\Rules\Queue\UniqueJobDeclaresUniqueForRule
	{
		return new Larastan\Larastan\Rules\Queue\UniqueJobDeclaresUniqueForRule;
	}


	public function createService01068(): Larastan\Larastan\Rules\Queue\UniqueJobDeclaresUniqueIdRule
	{
		return new Larastan\Larastan\Rules\Queue\UniqueJobDeclaresUniqueIdRule;
	}


	public function createService01069(): Larastan\Larastan\Rules\Queue\NoBatchedUniqueJobRule
	{
		return new Larastan\Larastan\Rules\Queue\NoBatchedUniqueJobRule;
	}


	public function createService01070(): Larastan\Larastan\Rules\Queue\JobWithModelPropertyDeclaresSerializesModelsRule
	{
		return new Larastan\Larastan\Rules\Queue\JobWithModelPropertyDeclaresSerializesModelsRule;
	}


	public function createService01071(): Larastan\Larastan\Rules\Queue\BatchedJobIsBatchableRule
	{
		return new Larastan\Larastan\Rules\Queue\BatchedJobIsBatchableRule;
	}


	public function createService01072(): Larastan\Larastan\Rules\Queue\BatchableJobChecksCancellationRule
	{
		return new Larastan\Larastan\Rules\Queue\BatchableJobChecksCancellationRule($this->getService('currentPhpVersionSimpleDirectParser'));
	}


	public function createService01073(): Larastan\Larastan\Rules\Queue\JobDispatchedInTransactionUsesAfterCommitRule
	{
		return new Larastan\Larastan\Rules\Queue\JobDispatchedInTransactionUsesAfterCommitRule($this->getService('reflectionProvider'));
	}


	public function createService01074(): Illuminate\Filesystem\Filesystem
	{
		return new Illuminate\Filesystem\Filesystem;
	}


	public function createServiceBetterReflectionProvider(): PHPStan\Reflection\BetterReflection\BetterReflectionProvider
	{
		return new PHPStan\Reflection\BetterReflection\BetterReflectionProvider(
			$this->getService('072'),
			$this->getService('0568'),
			$this->getService('betterReflectionReflector'),
			$this->getService('0556'),
			$this->getService('079'),
			$this->getService('03'),
			$this->getService('076'),
			$this->getService('075'),
			$this->getService('stubPhpDocProvider'),
			$this->getService('0565'),
			$this->getService('relativePathHelper'),
			$this->getService('030'),
			$this->getService('09'),
			$this->getService('012'),
			$this->getService('0895'),
			$this->getService('052'),
			$this->getParameter('universalObjectCratesClasses')
		);
	}


	public function createServiceBetterReflectionReflector(): PHPStan\Reflection\BetterReflection\Reflector\MemoizingReflector
	{
		return new PHPStan\Reflection\BetterReflection\Reflector\MemoizingReflector($this->getService('betterReflectionSourceLocator'));
	}


	public function createServiceBetterReflectionSourceLocator(): PHPStan\BetterReflection\SourceLocator\Type\SourceLocator
	{
		return $this->getService('065')->create();
	}


	public function createServiceCacheStorage(): PHPStan\Cache\FileCacheStorage
	{
		return new PHPStan\Cache\FileCacheStorage('/home/runner/work/snck-panel/snck-panel/laravel-panel/storage/framework/phpstan/cache/PHPStan');
	}


	public function createServiceCachedRichParser(): PHPStan\Parser\CachedParser
	{
		return new PHPStan\Parser\CachedParser($this->getService('currentPhpVersionRichParser'), 256, 4194304);
	}


	public function createServiceCachedSimpleParser(): PHPStan\Parser\CachedParser
	{
		return new PHPStan\Parser\CachedParser($this->getService('currentPhpVersionSimpleParser'), 256, 4194304);
	}


	public function createServiceContainer(): Container_93320a03c5
	{
		return $this;
	}


	public function createServiceCurrentPhpVersionLexer(): PhpParser\Lexer
	{
		return $this->getService('090')->create();
	}


	public function createServiceCurrentPhpVersionPhpParser(): PhpParser\ParserAbstract
	{
		return $this->getService('currentPhpVersionPhpParserFactory')->create();
	}


	public function createServiceCurrentPhpVersionPhpParserFactory(): PHPStan\Parser\PhpParserFactory
	{
		return new PHPStan\Parser\PhpParserFactory($this->getService('currentPhpVersionLexer'), $this->getService('03'));
	}


	public function createServiceCurrentPhpVersionRichParser(): PHPStan\Parser\RichParser
	{
		return new PHPStan\Parser\RichParser(
			$this->getService('currentPhpVersionPhpParser'),
			$this->getService('0888'),
			$this->getService('phpstan.extensionsCollection.PhpParser.NodeVisitor'),
			$this->getService('0279')
		);
	}


	public function createServiceCurrentPhpVersionSimpleDirectParser(): PHPStan\Parser\SimpleParser
	{
		return new PHPStan\Parser\SimpleParser($this->getService('currentPhpVersionPhpParser'), $this->getService('0888'));
	}


	public function createServiceCurrentPhpVersionSimpleParser(): PHPStan\Parser\CleaningParser
	{
		return new PHPStan\Parser\CleaningParser($this->getService('currentPhpVersionSimpleDirectParser'), $this->getService('03'));
	}


	public function createServiceDefaultAnalysisParser(): PHPStan\Parser\PathRoutingParser
	{
		return $this->getService('pathRoutingParser');
	}


	public function createServiceErrorFormatter__checkstyle(): PHPStan\Command\ErrorFormatter\CheckstyleErrorFormatter
	{
		return new PHPStan\Command\ErrorFormatter\CheckstyleErrorFormatter($this->getService('simpleRelativePathHelper'));
	}


	public function createServiceErrorFormatter__github(): PHPStan\Command\ErrorFormatter\GithubErrorFormatter
	{
		return new PHPStan\Command\ErrorFormatter\GithubErrorFormatter($this->getService('simpleRelativePathHelper'));
	}


	public function createServiceErrorFormatter__gitlab(): PHPStan\Command\ErrorFormatter\GitlabErrorFormatter
	{
		return new PHPStan\Command\ErrorFormatter\GitlabErrorFormatter($this->getService('simpleRelativePathHelper'));
	}


	public function createServiceErrorFormatter__json(): PHPStan\Command\ErrorFormatter\JsonErrorFormatter
	{
		return new PHPStan\Command\ErrorFormatter\JsonErrorFormatter(false);
	}


	public function createServiceErrorFormatter__junit(): PHPStan\Command\ErrorFormatter\JunitErrorFormatter
	{
		return new PHPStan\Command\ErrorFormatter\JunitErrorFormatter($this->getService('simpleRelativePathHelper'));
	}


	public function createServiceErrorFormatter__prettyJson(): PHPStan\Command\ErrorFormatter\JsonErrorFormatter
	{
		return new PHPStan\Command\ErrorFormatter\JsonErrorFormatter(true);
	}


	public function createServiceErrorFormatter__raw(): PHPStan\Command\ErrorFormatter\RawErrorFormatter
	{
		return new PHPStan\Command\ErrorFormatter\RawErrorFormatter;
	}


	public function createServiceErrorFormatter__table(): PHPStan\Command\ErrorFormatter\TableErrorFormatter
	{
		return new PHPStan\Command\ErrorFormatter\TableErrorFormatter(
			$this->getService('relativePathHelper'),
			$this->getService('simpleRelativePathHelper'),
			$this->getService('0339'),
			$this->getParameter('tipsOfTheDay'),
			$this->getParameter('editorUrl'),
			$this->getParameter('editorUrlTitle'),
			$this->getParameter('usedLevel')
		);
	}


	public function createServiceErrorFormatter__teamcity(): PHPStan\Command\ErrorFormatter\TeamcityErrorFormatter
	{
		return new PHPStan\Command\ErrorFormatter\TeamcityErrorFormatter($this->getService('simpleRelativePathHelper'));
	}


	public function createServiceExceptionTypeResolver(): PHPStan\Rules\Exceptions\ExceptionTypeResolver
	{
		return $this->getService('0119');
	}


	public function createServiceFileExcluderAnalyse(): PHPStan\File\FileExcluder
	{
		return $this->getService('013')->createAnalyseFileExcluder();
	}


	public function createServiceFileExcluderScan(): PHPStan\File\FileExcluder
	{
		return $this->getService('013')->createScanFileExcluder();
	}


	public function createServiceFileFinderAnalyse(): PHPStan\File\FileFinder
	{
		return new PHPStan\File\FileFinder(
			$this->getService('fileExcluderAnalyse'),
			$this->getService('09'),
			['php'],
			$this->getService('011')
		);
	}


	public function createServiceFileFinderScan(): PHPStan\File\FileFinder
	{
		return new PHPStan\File\FileFinder(
			$this->getService('fileExcluderScan'),
			$this->getService('09'),
			['php'],
			$this->getService('011')
		);
	}


	public function createServiceFreshStubParser(): PHPStan\Parser\StubParser
	{
		return new PHPStan\Parser\StubParser($this->getService('php8PhpParser'), $this->getService('0888'));
	}


	public function createServiceIamcalSqlParser(): Larastan\Larastan\SQL\IamcalSqlParser
	{
		return new Larastan\Larastan\SQL\IamcalSqlParser;
	}


	public function createServiceMigrationsParser(): PHPStan\Parser\CachedParser
	{
		return new PHPStan\Parser\CachedParser($this->getService('currentPhpVersionSimpleDirectParser'), 256);
	}


	public function createServiceParentDirectoryRelativePathHelper(): PHPStan\File\ParentDirectoryRelativePathHelper
	{
		return new PHPStan\File\ParentDirectoryRelativePathHelper($this->getParameter('currentWorkingDirectory'));
	}


	public function createServicePathRoutingParser(): PHPStan\Parser\PathRoutingParser
	{
		return new PHPStan\Parser\PathRoutingParser(
			$this->getService('09'),
			$this->getService('cachedRichParser'),
			$this->getService('cachedSimpleParser'),
			$this->getService('php8Parser'),
			$this->getParameter('singleReflectionFile')
		);
	}


	public function createServicePhp8Lexer(): PhpParser\Lexer\Emulative
	{
		return $this->getService('090')->createEmulative();
	}


	public function createServicePhp8Parser(): PHPStan\Parser\SimpleParser
	{
		return new PHPStan\Parser\SimpleParser($this->getService('php8PhpParser'), $this->getService('0888'));
	}


	public function createServicePhp8PhpParser(): PhpParser\Parser\Php8
	{
		return new PhpParser\Parser\Php8($this->getService('php8Lexer'));
	}


	public function createServicePhpParserDecorator(): PHPStan\Parser\PhpParserDecorator
	{
		return new PHPStan\Parser\PhpParserDecorator($this->getService('defaultAnalysisParser'));
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Analyser__ExprHandler(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection($this->getService('0333'), 'phpstan.exprHandler');
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Analyser__IgnoreErrorExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection($this->getService('0333'), 'phpstan.ignoreErrorExtension');
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Analyser__PerFileAnalysisResettable(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection($this->getService('0333'), 'phpstan.perFileAnalysisResettable');
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Analyser__ResultCache__ResultCacheMetaExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection($this->getService('0333'), 'phpstan.resultCacheMetaExtension');
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Analyser__ResultCache__ResultCacheValueExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection($this->getService('0333'), 'phpstan.resultCacheValueExtension');
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Analyser__StmtHandler(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection($this->getService('0333'), 'phpstan.stmtHandler');
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Classes__ForbiddenClassNameExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.forbiddenClassNamesExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Collectors__Collector(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection($this->getService('0333'), 'phpstan.collector');
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Diagnose__DiagnoseExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection($this->getService('0333'), 'phpstan.diagnoseExtension');
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__PhpDoc__StubFilesExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection($this->getService('0333'), 'phpstan.stubFilesExtension');
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__PhpDoc__TypeNodeResolverExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.phpDoc.typeNodeResolverExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Reflection__AdditionalConstructorsExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.additionalConstructorsExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Reflection__AllowedSubTypesClassReflectionExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.broker.allowedSubTypesClassReflectionExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Reflection__Deprecation__ClassConstantDeprecationExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.classConstantDeprecationExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Reflection__Deprecation__ClassDeprecationExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection($this->getService('0333'), 'phpstan.classDeprecationExtension');
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Reflection__Deprecation__ConstantDeprecationExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.constantDeprecationExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Reflection__Deprecation__EnumCaseDeprecationExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.enumCaseDeprecationExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Reflection__Deprecation__FunctionDeprecationExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.functionDeprecationExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Reflection__Deprecation__MethodDeprecationExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection($this->getService('0333'), 'phpstan.methodDeprecationExtension');
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Reflection__Deprecation__PropertyDeprecationExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.propertyDeprecationExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Reflection__MethodsClassReflectionExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.broker.methodsClassReflectionExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Reflection__PropertiesClassReflectionExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.broker.propertiesClassReflectionExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Rules__Constants__AlwaysUsedClassConstantsExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.constants.alwaysUsedClassConstantsExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Rules__Methods__AlwaysUsedMethodExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.methods.alwaysUsedMethodExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Rules__Properties__ReadWritePropertiesExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.properties.readWriteExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Rules__RestrictedUsage__RestrictedClassConstantUsageExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.restrictedClassConstantUsageExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Rules__RestrictedUsage__RestrictedClassNameUsageExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.restrictedClassNameUsageExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Rules__RestrictedUsage__RestrictedFunctionUsageExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.restrictedFunctionUsageExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Rules__RestrictedUsage__RestrictedMethodUsageExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.restrictedMethodUsageExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Rules__RestrictedUsage__RestrictedPropertyUsageExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.restrictedPropertyUsageExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Rules__Rule(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection($this->getService('0333'), 'phpstan.rules.rule');
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Type__DynamicFunctionReturnTypeExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.broker.dynamicFunctionReturnTypeExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Type__DynamicFunctionThrowTypeExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.dynamicFunctionThrowTypeExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Type__DynamicMethodReturnTypeExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.broker.dynamicMethodReturnTypeExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Type__DynamicMethodThrowTypeExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.dynamicMethodThrowTypeExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Type__DynamicStaticMethodReturnTypeExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.broker.dynamicStaticMethodReturnTypeExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Type__DynamicStaticMethodThrowTypeExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.dynamicStaticMethodThrowTypeExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Type__ExpressionTypeResolverExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.broker.expressionTypeResolverExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Type__FunctionParameterClosureThisExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.functionParameterClosureThisExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Type__FunctionParameterClosureTypeExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.functionParameterClosureTypeExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Type__FunctionParameterOutTypeExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.functionParameterOutTypeExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Type__FunctionTypeSpecifyingExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.typeSpecifier.functionTypeSpecifyingExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Type__MethodParameterClosureThisExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.methodParameterClosureThisExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Type__MethodParameterClosureTypeExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.methodParameterClosureTypeExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Type__MethodParameterOutTypeExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.methodParameterOutTypeExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Type__MethodTypeSpecifyingExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.typeSpecifier.methodTypeSpecifyingExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Type__OperatorTypeSpecifyingExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.broker.operatorTypeSpecifyingExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Type__StaticMethodParameterClosureThisExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.staticMethodParameterClosureThisExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Type__StaticMethodParameterClosureTypeExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.staticMethodParameterClosureTypeExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Type__StaticMethodParameterOutTypeExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.staticMethodParameterOutTypeExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Type__StaticMethodTypeSpecifyingExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.typeSpecifier.staticMethodTypeSpecifyingExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PHPStan__Type__UnaryOperatorTypeSpecifyingExtension(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.broker.unaryOperatorTypeSpecifyingExtension'
		);
	}


	public function createServicePhpstan__extensionsCollection__PhpParser__NodeVisitor(): PHPStan\DependencyInjection\LazyExtensionsCollection
	{
		return new PHPStan\DependencyInjection\LazyExtensionsCollection(
			$this->getService('0333'),
			'phpstan.parser.richParserNodeVisitor'
		);
	}


	public function createServicePhpstanDiagnoseExtension(): PHPStan\Diagnose\PHPStanDiagnoseExtension
	{
		return new PHPStan\Diagnose\PHPStanDiagnoseExtension(
			$this->getService('03'),
			$this->getParameter('phpVersion'),
			$this->getService('09'),
			$this->getParameter('composerAutoloaderProjectPaths'),
			$this->getParameter('allConfigFiles'),
			$this->getService('04'),
			$this->getService('simpleRelativePathHelper')
		);
	}


	public function createServiceReflectionProvider(): PHPStan\Reflection\ReflectionProvider
	{
		return $this->getService('reflectionProviderFactory')->create();
	}


	public function createServiceReflectionProviderFactory(): PHPStan\Reflection\ReflectionProvider\ReflectionProviderFactory
	{
		return new PHPStan\Reflection\ReflectionProvider\ReflectionProviderFactory($this->getService('betterReflectionProvider'));
	}


	public function createServiceRegistry(): PHPStan\Rules\LazyRegistry
	{
		return new PHPStan\Rules\LazyRegistry($this->getService('phpstan.extensionsCollection.PHPStan.Rules.Rule'));
	}


	public function createServiceRelativePathHelper(): PHPStan\File\FuzzyRelativePathHelper
	{
		return new PHPStan\File\FuzzyRelativePathHelper(
			$this->getService('parentDirectoryRelativePathHelper'),
			$this->getParameter('currentWorkingDirectory'),
			$this->getParameter('analysedPaths')
		);
	}


	public function createServiceRules__0(): Larastan\Larastan\Rules\UselessConstructs\NoUselessWithFunctionCallsRule
	{
		return new Larastan\Larastan\Rules\UselessConstructs\NoUselessWithFunctionCallsRule;
	}


	public function createServiceRules__1(): Larastan\Larastan\Rules\UselessConstructs\NoUselessValueFunctionCallsRule
	{
		return new Larastan\Larastan\Rules\UselessConstructs\NoUselessValueFunctionCallsRule;
	}


	public function createServiceRules__2(): Larastan\Larastan\Rules\DeferrableServiceProviderMissingProvidesRule
	{
		return new Larastan\Larastan\Rules\DeferrableServiceProviderMissingProvidesRule;
	}


	public function createServiceRules__3(): Larastan\Larastan\Rules\ConsoleCommand\UndefinedArgumentOrOptionRule
	{
		return new Larastan\Larastan\Rules\ConsoleCommand\UndefinedArgumentOrOptionRule($this->getService('01053'));
	}


	public function createServiceSimpleRelativePathHelper(): PHPStan\File\SimpleRelativePathHelper
	{
		return new PHPStan\File\SimpleRelativePathHelper($this->getParameter('currentWorkingDirectory'));
	}


	public function createServiceSqlParser(): Larastan\Larastan\SQL\SqlParser
	{
		return $this->getService('sqlParserFactory')->create();
	}


	public function createServiceSqlParserFactory(): Larastan\Larastan\SQL\SqlParserFactory
	{
		return new Larastan\Larastan\SQL\SqlParserFactory($this->getService('iamcalSqlParser'));
	}


	public function createServiceStubFileTypeMapper(): PHPStan\Type\FileTypeMapper
	{
		return new PHPStan\Type\FileTypeMapper(
			$this->getService('054'),
			$this->getService('stubParser'),
			$this->getService('019'),
			$this->getService('021'),
			$this->getService('030'),
			$this->getService('09'),
			$this->getService('029'),
			$this->getService('07'),
			2048,
			512
		);
	}


	public function createServiceStubParser(): PHPStan\Parser\CachedParser
	{
		return new PHPStan\Parser\CachedParser($this->getService('freshStubParser'), 256, 4194304);
	}


	public function createServiceStubPhpDocProvider(): PHPStan\PhpDoc\StubPhpDocProvider
	{
		return new PHPStan\PhpDoc\StubPhpDocProvider(
			$this->getService('stubParser'),
			$this->getService('stubFileTypeMapper'),
			$this->getService('017')
		);
	}


	public function createServiceTypeSpecifier(): PHPStan\Analyser\TypeSpecifier
	{
		return $this->getService('typeSpecifierFactory')->create();
	}


	public function createServiceTypeSpecifierFactory(): PHPStan\Analyser\TypeSpecifierFactory
	{
		return new PHPStan\Analyser\TypeSpecifierFactory($this->getService('0333'));
	}


	public function initialize(): void
	{
	}


	protected function getStaticParameters(): array
	{
		return [
			'bootstrapFiles' => [
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/ReflectionUnionType.php',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/ReflectionAttribute.php',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/Attribute85.php',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/ReflectionIntersectionType.php',
				'/home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/larastan/larastan/bootstrap.php',
			],
			'excludePaths' => [
				'analyseAndScan' => ['/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Livewire/**/*.blade.php'],
				'analyse' => [],
			],
			'level' => 5,
			'paths' => [
				'/home/runner/work/snck-panel/snck-panel/laravel-panel/app',
				'/home/runner/work/snck-panel/snck-panel/laravel-panel/routes',
			],
			'exceptions' => [
				'implicitThrows' => true,
				'reportUncheckedExceptionDeadCatch' => true,
				'uncheckedExceptionRegexes' => [],
				'uncheckedExceptionClasses' => ['Error'],
				'checkedExceptionRegexes' => [],
				'checkedExceptionClasses' => [],
				'check' => [
					'missingCheckedExceptionInThrows' => false,
					'tooWideThrowType' => true,
					'tooWideImplicitThrowType' => false,
					'throwTypeCovariance' => false,
				],
			],
			'featureToggles' => [
				'bleedingEdge' => false,
				'checkNonStringableDynamicAccess' => false,
				'checkParameterCastableToNumberFunctions' => false,
				'skipCheckGenericClasses' => [
					'DOMNamedNodeMap',
					'ParentIterator',
					'RecursiveCachingIterator',
					'RecursiveFilterIterator',
					'RecursiveRegexIterator',
					'ReflectionObject',
				],
				'stricterFunctionMap' => false,
				'reportPreciseLineForUnusedFunctionParameter' => false,
				'checkPrintfParameterTypes' => false,
				'internalTag' => false,
				'newStaticInAbstractClassStaticMethod' => false,
				'checkExtensionsForComparisonOperators' => false,
				'checkGenericIterableClasses' => false,
				'reportTooWideBool' => false,
				'rawMessageInBaseline' => false,
				'reportNestedTooWideType' => false,
				'assignToByRefForeachExpr' => false,
				'curlSetOptArrayTypes' => false,
				'magicDirInInclude' => false,
				'checkDateIntervalConstructor' => false,
				'reportMethodPurityOverride' => false,
				'checkDynamicConstantNameValues' => false,
				'unusedLabel' => false,
				'newOnNonObject' => false,
				'unnecessaryNullCoalesce' => false,
				'finiteTypesInHaystack' => false,
				'switchConditionAlwaysFalse' => false,
				'checkImportedClassNameCase' => false,
				'sortWithoutEffect' => false,
				'unresolvedTemplateArguments' => false,
				'closureSignaturesFromUsages' => false,
				'staticVariablesFromUsages' => false,
				'backedEnumFromParameterType' => false,
				'unusedVariable' => false,
				'unusedParameters' => false,
				'reuseCollectedDataRuleErrors' => false,
			],
			'fileExtensions' => ['php'],
			'checkAdvancedIsset' => true,
			'reportAlwaysTrueInLastCondition' => false,
			'checkClassCaseSensitivity' => true,
			'checkExplicitMixed' => false,
			'checkImplicitMixed' => false,
			'checkFunctionArgumentTypes' => true,
			'checkFunctionNameCase' => false,
			'checkInternalClassCaseSensitivity' => false,
			'checkMissingCallableSignature' => false,
			'checkMissingVarTagTypehint' => false,
			'checkArgumentsPassedByReference' => true,
			'checkMaybeUndefinedVariables' => true,
			'checkNullables' => false,
			'checkThisOnly' => false,
			'checkUnionTypes' => false,
			'checkBenevolentUnionTypes' => false,
			'checkExplicitMixedMissingReturn' => false,
			'checkPhpDocMissingReturn' => true,
			'checkPhpDocMethodSignatures' => true,
			'checkExtraArguments' => true,
			'checkMissingTypehints' => false,
			'checkTooWideParameterOutInProtectedAndPublicMethods' => false,
			'checkTooWideReturnTypesInProtectedAndPublicMethods' => false,
			'checkTooWideThrowTypesInProtectedAndPublicMethods' => false,
			'checkUninitializedProperties' => false,
			'checkDynamicProperties' => false,
			'strictRulesInstalled' => false,
			'deprecationRulesInstalled' => false,
			'inferPrivatePropertyTypeFromConstructor' => false,
			'checkStrictPrintfPlaceholderTypes' => false,
			'reportMaybes' => false,
			'reportMaybesInMethodSignatures' => false,
			'reportMaybesInPropertyPhpDocTypes' => false,
			'reportStaticMethodSignatures' => false,
			'reportWrongPhpDocTypeInVarTag' => false,
			'reportAnyTypeWideningInVarTag' => false,
			'reportNonIntStringArrayKey' => false,
			'reportUnsafeArrayStringKeyCasting' => null,
			'reportPossiblyNonexistentGeneralArrayOffset' => false,
			'reportPossiblyNonexistentConstantArrayOffset' => false,
			'checkMissingOverrideMethodAttribute' => false,
			'checkMissingOverridePropertyAttribute' => null,
			'mixinExcludeClasses' => ['Eloquent'],
			'scanFiles' => [],
			'scanDirectories' => [],
			'parallel' => [
				'jobSize' => 20,
				'processTimeout' => 600.0,
				'maximumNumberOfProcesses' => 'auto',
				'minimumNumberOfJobsPerProcess' => 2,
				'buffer' => 134217728,
				'loadLimit' => 1.0,
			],
			'phpVersion' => null,
			'polluteScopeWithLoopInitialAssignments' => true,
			'polluteScopeWithAlwaysIterableForeach' => true,
			'polluteScopeWithBlock' => true,
			'propertyAlwaysWrittenTags' => [],
			'propertyAlwaysReadTags' => [],
			'additionalConstructors' => [],
			'treatPhpDocTypesAsCertain' => true,
			'usePathConstantsAsConstantString' => false,
			'rememberPossiblyImpureFunctionValues' => true,
			'tips' => ['discoveringSymbols' => true, 'treatPhpDocTypesAsCertain' => true, 'possiblyImpure' => true],
			'tipsOfTheDay' => true,
			'reportMagicMethods' => true,
			'reportMagicProperties' => true,
			'ignoreErrors' => [
				[
					'message' => '#^Access to an undefined property Illuminate\\\Database\\\Eloquent\\\Model\:\:\$username\.$#',
					'identifier' => 'property.notFound',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Http/Controllers/ApiKeyController.php',
				],
				[
					'message' => '#^Cannot call method toISOString\(\) on string\.$#',
					'identifier' => 'method.nonObject',
					'count' => 3,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Http/Controllers/ApiKeyController.php',
				],
				[
					'message' => '#^Call to an undefined method Laravel\\\Socialite\\\Contracts\\\Provider\:\:scopes\(\)\.$#',
					'identifier' => 'method.notFound',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Http/Controllers/AuthController.php',
				],
				[
					'message' => '#^Call to an undefined method Laravel\\\Socialite\\\Contracts\\\User\:\:getRaw\(\)\.$#',
					'identifier' => 'method.notFound',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Http/Controllers/AuthController.php',
				],
				[
					'message' => '#^Parameter \#1 \$boolean of function abort_unless expects bool, stdClass\|null given\.$#',
					'identifier' => 'argument.type',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Http/Controllers/AuthController.php',
				],
				[
					'message' => '#^Parameter \#1 \$node of method App\\\Services\\\NodeClient\:\:createBackup\(\) expects App\\\Models\\\Node, Illuminate\\\Database\\\Eloquent\\\Model\|null given\.$#',
					'identifier' => 'argument.type',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Http/Controllers/BackupController.php',
				],
				[
					'message' => '#^Parameter \#1 \$node of method App\\\Services\\\NodeClient\:\:deleteBackup\(\) expects App\\\Models\\\Node, Illuminate\\\Database\\\Eloquent\\\Model\|null given\.$#',
					'identifier' => 'argument.type',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Http/Controllers/BackupController.php',
				],
				[
					'message' => '#^Parameter \#1 \$node of method App\\\Services\\\NodeClient\:\:downloadBackup\(\) expects App\\\Models\\\Node, Illuminate\\\Database\\\Eloquent\\\Model\|null given\.$#',
					'identifier' => 'argument.type',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Http/Controllers/BackupController.php',
				],
				[
					'message' => '#^Parameter \#1 \$node of method App\\\Services\\\NodeClient\:\:listBackups\(\) expects App\\\Models\\\Node, Illuminate\\\Database\\\Eloquent\\\Model\|null given\.$#',
					'identifier' => 'argument.type',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Http/Controllers/BackupController.php',
				],
				[
					'message' => '#^Parameter \#1 \$node of method App\\\Services\\\NodeClient\:\:deleteServer\(\) expects App\\\Models\\\Node, Illuminate\\\Database\\\Eloquent\\\Model\|null given\.$#',
					'identifier' => 'argument.type',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Http/Controllers/ServerController.php',
				],
				[
					'message' => '#^Parameter \#1 \$node of method App\\\Services\\\NodeClient\:\:serverAction\(\) expects App\\\Models\\\Node, Illuminate\\\Database\\\Eloquent\\\Model\|null given\.$#',
					'identifier' => 'argument.type',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Http/Controllers/ServerController.php',
				],
				[
					'message' => '#^Parameter \#1 \$node of method App\\\Services\\\NodeClient\:\:serverStatus\(\) expects App\\\Models\\\Node, Illuminate\\\Database\\\Eloquent\\\Model\|null given\.$#',
					'identifier' => 'argument.type',
					'count' => 2,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Http/Controllers/ServerController.php',
				],
				[
					'message' => '#^Parameter \#1 \$node of method App\\\Services\\\NodeClient\:\:downloadServerFile\(\) expects App\\\Models\\\Node, Illuminate\\\Database\\\Eloquent\\\Model\|null given\.$#',
					'identifier' => 'argument.type',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Http/Controllers/ServerFilesController.php',
				],
				[
					'message' => '#^Parameter \#1 \$node of method App\\\Services\\\NodeClient\:\:serverLogs\(\) expects App\\\Models\\\Node, Illuminate\\\Database\\\Eloquent\\\Model\|null given\.$#',
					'identifier' => 'argument.type',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Http/Controllers/ServerFilesController.php',
				],
				[
					'message' => '#^Parameter \#1 \$node of method App\\\Services\\\NodeClient\:\:serverRequest\(\) expects App\\\Models\\\Node, Illuminate\\\Database\\\Eloquent\\\Model\|null given\.$#',
					'identifier' => 'argument.type',
					'count' => 13,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Http/Controllers/ServerFilesController.php',
				],
				[
					'message' => '#^Constant App\\\Http\\\Controllers\\\SettingsController\:\:OWNER_KEYS is unused\.$#',
					'identifier' => 'classConstant.unused',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Http/Controllers/SettingsController.php',
				],
				[
					'message' => '#^Constant App\\\Http\\\Controllers\\\SettingsController\:\:PUBLIC_KEYS is unused\.$#',
					'identifier' => 'classConstant.unused',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Http/Controllers/SettingsController.php',
				],
				[
					'message' => '#^Access to an undefined property App\\\Models\\\User\:\:\$pivot\.$#',
					'identifier' => 'property.notFound',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Livewire/ServerDetail.php',
				],
				[
					'message' => '#^Parameter \#1 \$callback of method Illuminate\\\Database\\\Eloquent\\\Collection\<int,Illuminate\\\Database\\\Eloquent\\\Model\>\:\:map\(\) expects callable\(Illuminate\\\Database\\\Eloquent\\\Model, int\)\: array\{id\: string, username\: string\|null, email\: string\|null, permissions\: array\<mixed\>\}, Closure\(App\\\Models\\\User\)\: array\{id\: string, username\: string\|null, email\: string\|null, permissions\: array\<mixed\>\} given\.$#',
					'identifier' => 'argument.type',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Livewire/ServerDetail.php',
				],
				[
					'message' => '#^Parameter \#1 \$node of method App\\\Services\\\NodeClient\:\:createBackup\(\) expects App\\\Models\\\Node, Illuminate\\\Database\\\Eloquent\\\Model\|null given\.$#',
					'identifier' => 'argument.type',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Livewire/ServerDetail.php',
				],
				[
					'message' => '#^Parameter \#1 \$node of method App\\\Services\\\NodeClient\:\:deleteBackup\(\) expects App\\\Models\\\Node, Illuminate\\\Database\\\Eloquent\\\Model\|null given\.$#',
					'identifier' => 'argument.type',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Livewire/ServerDetail.php',
				],
				[
					'message' => '#^Parameter \#1 \$node of method App\\\Services\\\NodeClient\:\:listBackups\(\) expects App\\\Models\\\Node, Illuminate\\\Database\\\Eloquent\\\Model\|null given\.$#',
					'identifier' => 'argument.type',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Livewire/ServerDetail.php',
				],
				[
					'message' => '#^Parameter \#1 \$node of method App\\\Services\\\NodeClient\:\:serverAction\(\) expects App\\\Models\\\Node, Illuminate\\\Database\\\Eloquent\\\Model\|null given\.$#',
					'identifier' => 'argument.type',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Livewire/ServerDetail.php',
				],
				[
					'message' => '#^Parameter \#1 \$node of method App\\\Services\\\NodeClient\:\:serverLogs\(\) expects App\\\Models\\\Node, Illuminate\\\Database\\\Eloquent\\\Model\|null given\.$#',
					'identifier' => 'argument.type',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Livewire/ServerDetail.php',
				],
				[
					'message' => '#^Parameter \#1 \$node of method App\\\Services\\\NodeClient\:\:serverRequest\(\) expects App\\\Models\\\Node, Illuminate\\\Database\\\Eloquent\\\Model\|null given\.$#',
					'identifier' => 'argument.type',
					'count' => 13,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Livewire/ServerDetail.php',
				],
				[
					'message' => '#^Parameter \#1 \$node of method App\\\Services\\\NodeClient\:\:serverStatus\(\) expects App\\\Models\\\Node, Illuminate\\\Database\\\Eloquent\\\Model\|null given\.$#',
					'identifier' => 'argument.type',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Livewire/ServerDetail.php',
				],
				[
					'message' => '#^Using nullsafe property access "\?\-\>payload" on left side of \?\? is unnecessary\. Use \-\> instead\.$#',
					'identifier' => 'nullsafe.neverNull',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Services/DiscordRewardsService.php',
				],
				[
					'message' => '#^Match expression does not handle remaining value\: string$#',
					'identifier' => 'match.unhandled',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Services/NodeClient.php',
				],
				[
					'message' => '#^Offset 0 on array\{list\<string\>\} on left side of \?\? always exists and is not nullable\.$#',
					'identifier' => 'nullCoalesce.offset',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Services/PlayitService.php',
				],
				[
					'message' => '#^Call to function is_array\(\) with array will always evaluate to true\.$#',
					'identifier' => 'function.alreadyNarrowedType',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Services/PluginManagerService.php',
				],
				[
					'message' => '#^Call to function is_string\(\) with string will always evaluate to true\.$#',
					'identifier' => 'function.alreadyNarrowedType',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Services/PluginManagerService.php',
				],
				[
					'message' => '#^Parameter \#1 \$array \(array\{0\?\: non\-falsy\-string\}\) of array_values is already a list, call has no effect\.$#',
					'identifier' => 'arrayValues.list',
					'count' => 2,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Services/PluginManagerService.php',
				],
				[
					'message' => '#^Parameter \#1 \$node of method App\\\Services\\\NodeClient\:\:serverRequest\(\) expects App\\\Models\\\Node, Illuminate\\\Database\\\Eloquent\\\Model\|null given\.$#',
					'identifier' => 'argument.type',
					'count' => 4,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Services/PluginManagerService.php',
				],
				[
					'message' => '#^Parameter \#1 \$node of method App\\\Services\\\NodeClient\:\:serverStatus\(\) expects App\\\Models\\\Node, Illuminate\\\Database\\\Eloquent\\\Model\|null given\.$#',
					'identifier' => 'argument.type',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Services/PluginManagerService.php',
				],
				[
					'message' => '#^Cannot call method greaterThan\(\) on string\.$#',
					'identifier' => 'method.nonObject',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Services/ServerProvisioner.php',
				],
				[
					'message' => '#^Parameter \#1 \$node of method App\\\Services\\\NodeClient\:\:createServer\(\) expects App\\\Models\\\Node, Illuminate\\\Database\\\Eloquent\\\Model\|null given\.$#',
					'identifier' => 'argument.type',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Services/ServerProvisioner.php',
				],
				[
					'message' => '#^Parameter \#1 \$node of method App\\\Services\\\NodeClient\:\:serverStatus\(\) expects App\\\Models\\\Node, Illuminate\\\Database\\\Eloquent\\\Model\|null given\.$#',
					'identifier' => 'argument.type',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Services/ServerProvisioner.php',
				],
				[
					'message' => '#^Access to an undefined property Illuminate\\\Database\\\Eloquent\\\Model\:\:\$fqdn\.$#',
					'identifier' => 'property.notFound',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Services/SftpCredentialService.php',
				],
				[
					'message' => '#^Access to an undefined property Illuminate\\\Database\\\Eloquent\\\Model\:\:\$hostname\.$#',
					'identifier' => 'property.notFound',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Services/SftpCredentialService.php',
				],
				[
					'message' => '#^Access to an undefined property Illuminate\\\Database\\\Eloquent\\\Model\:\:\$public_ip\.$#',
					'identifier' => 'property.notFound',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Services/SftpCredentialService.php',
				],
				[
					'message' => '#^Access to an undefined property Illuminate\\\Database\\\Eloquent\\\Model\:\:\$sftp_port\.$#',
					'identifier' => 'property.notFound',
					'count' => 1,
					'path' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/app/Services/SftpCredentialService.php',
				],
			],
			'internalErrorsCountLimit' => 50,
			'cache' => [
				'nodesByStringCountMax' => 256,
				'nodesByStringSourceBytesMax' => 4194304,
				'resolvedPhpDocBlockCacheCountMax' => 2048,
				'nameScopeMapMemoryCacheCountMax' => 512,
				'phpStormStubsNodesCountMax' => 128,
				'memberCacheKeysMax' => 2048,
				'resolvedLocalTypeAliasesCountMax' => 2048,
			],
			'reportUnmatchedIgnoredErrors' => true,
			'reportIgnoresWithoutComments' => false,
			'typeAliases' => [],
			'universalObjectCratesClasses' => ['stdClass', 'Illuminate\Http\Request', 'Illuminate\Support\Optional'],
			'stubFiles' => [
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/Memcached.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/Redis.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/ReflectionAttribute.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/ReflectionClassConstant.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/ReflectionFunctionAbstract.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/ReflectionMethod.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/ReflectionParameter.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/ReflectionProperty.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/ReflectionType.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/iterable.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/ArrayObject.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/WeakReference.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/SensitiveParameterValue.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/ImagickPixel.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/PDOStatement.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/date.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/ibm_db2.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/mysqli.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/zip.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/dom.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/spl.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/SplObjectStorage.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/Exception.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/arrayFunctions.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/core.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/typeCheckingFunctions.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/Countable.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/file.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/stream_socket_client.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/stream_socket_server.stub',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/stubs/ctype.stub',
			],
			'earlyTerminatingMethodCalls' => [],
			'earlyTerminatingFunctionCalls' => ['abort', 'dd'],
			'resultCachePath' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/storage/framework/phpstan/resultCache.php',
			'resultCacheSkipIfOlderThanDays' => 7,
			'resultCacheChecksProjectExtensionFilesDependencies' => false,
			'dynamicConstantNames' => [
				'ICONV_IMPL',
				'LIBXML_VERSION',
				'LIBXML_DOTTED_VERSION',
				'Memcached::HAVE_ENCODING',
				'Memcached::HAVE_IGBINARY',
				'Memcached::HAVE_JSON',
				'Memcached::HAVE_MSGPACK',
				'Memcached::HAVE_SASL',
				'Memcached::HAVE_SESSION',
				'PHP_VERSION',
				'PHP_MAJOR_VERSION',
				'PHP_MINOR_VERSION',
				'PHP_RELEASE_VERSION',
				'PHP_VERSION_ID',
				'PHP_EXTRA_VERSION',
				'PHP_WINDOWS_VERSION_MAJOR',
				'PHP_WINDOWS_VERSION_MINOR',
				'PHP_WINDOWS_VERSION_BUILD',
				'PHP_ZTS',
				'PHP_DEBUG',
				'PHP_MAXPATHLEN',
				'PHP_OS',
				'PHP_OS_FAMILY',
				'PHP_SAPI',
				'PHP_EOL',
				'PHP_INT_MAX',
				'PHP_INT_MIN',
				'PHP_INT_SIZE',
				'PHP_FLOAT_DIG',
				'PHP_FLOAT_EPSILON',
				'PHP_FLOAT_MIN',
				'PHP_FLOAT_MAX',
				'DEFAULT_INCLUDE_PATH',
				'PEAR_INSTALL_DIR',
				'PEAR_EXTENSION_DIR',
				'PHP_EXTENSION_DIR',
				'PHP_PREFIX',
				'PHP_BINDIR',
				'PHP_BINARY',
				'PHP_MANDIR',
				'PHP_LIBDIR',
				'PHP_DATADIR',
				'PHP_SYSCONFDIR',
				'PHP_LOCALSTATEDIR',
				'PHP_CONFIG_FILE_PATH',
				'PHP_CONFIG_FILE_SCAN_DIR',
				'PHP_SHLIB_SUFFIX',
				'PHP_FD_SETSIZE',
				'OPENSSL_VERSION_NUMBER',
				'ZEND_DEBUG_BUILD',
				'ZEND_THREAD_SAFE',
				'E_ALL',
			],
			'customRulesetUsed' => false,
			'editorUrl' => null,
			'editorUrlTitle' => null,
			'errorFormat' => null,
			'sourceLocatorPlaygroundMode' => false,
			'__validate' => true,
			'parametersNotInvalidatingCache' => [
				['parameters', 'editorUrl'],
				['parameters', 'editorUrlTitle'],
				['parameters', 'errorFormat'],
				['parameters', 'ignoreErrors'],
				['parameters', 'reportUnmatchedIgnoredErrors'],
				['parameters', 'tipsOfTheDay'],
				['parameters', 'parallel'],
				['parameters', 'internalErrorsCountLimit'],
				['parameters', 'cache'],
				['parameters', 'memoryLimitFile'],
				['parameters', 'tmpDir'],
				['parameters', 'pro'],
				'parametersSchema',
			],
			'checkOctaneCompatibility' => false,
			'noEnvCallsOutsideOfConfig' => true,
			'noModelMake' => true,
			'noImplicitQueryBuilderCall' => false,
			'noUnnecessaryCollectionCall' => true,
			'noUnnecessaryCollectionCallOnly' => [],
			'noUnnecessaryCollectionCallExcept' => [],
			'noUnnecessaryEnumerableToArrayCalls' => false,
			'squashedMigrationsPath' => [],
			'databaseMigrationsPath' => [],
			'disableMigrationScan' => false,
			'disableSchemaScan' => false,
			'configDirectories' => [],
			'viewDirectories' => [],
			'translationDirectories' => [],
			'checkModelProperties' => false,
			'checkUnusedViews' => false,
			'checkMissingTranslations' => false,
			'checkModelAppends' => true,
			'checkModelMethodVisibility' => false,
			'generalizeEnvReturnType' => false,
			'checkConfigTypes' => false,
			'checkAuthCallsWhenInRequestScope' => false,
			'parseModelCastsMethod' => false,
			'enableMigrationCache' => true,
			'checkUniqueJobUniqueFor' => false,
			'checkUniqueJobUniqueId' => false,
			'noBatchedUniqueJob' => false,
			'checkJobSerializesModels' => false,
			'checkBatchedJobIsBatchable' => false,
			'checkBatchableJobChecksCancellation' => false,
			'checkDispatchInTransactionAfterCommit' => false,
			'tmpDir' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/storage/framework/phpstan',
			'debugMode' => true,
			'productionMode' => false,
			'tempDir' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/storage/framework/phpstan',
			'rootDir' => '/home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan',
			'currentWorkingDirectory' => '/home/runner/work/snck-panel/snck-panel/laravel-panel',
			'cliArgumentsVariablesRegistered' => true,
			'additionalConfigFiles' => [
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/conf/config.level5.neon',
				'/home/runner/work/snck-panel/snck-panel/laravel-panel/phpstan.neon',
			],
			'allConfigFiles' => [
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/conf/parametersSchema.neon',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/conf/config.level5.neon',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/conf/config.level4.neon',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/conf/config.level3.neon',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/conf/config.level2.neon',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/conf/config.level1.neon',
				'phar:///home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/phpstan/phpstan/phpstan.phar/conf/config.level0.neon',
				'/home/runner/work/snck-panel/snck-panel/laravel-panel/phpstan.neon',
				'/home/runner/work/snck-panel/snck-panel/laravel-panel/vendor/larastan/larastan/extension.neon',
				'/home/runner/work/snck-panel/snck-panel/laravel-panel/phpstan-baseline.neon',
			],
			'composerAutoloaderProjectPaths' => ['/home/runner/work/snck-panel/snck-panel/laravel-panel'],
			'generateBaselineFile' => null,
			'usedLevel' => '5',
			'cliAutoloadFile' => null,
			'env' => [
				'SHELL' => '/bin/bash',
				'SELENIUM_JAR_PATH' => '/usr/share/java/selenium-server.jar',
				'GITHUB_WORKSPACE' => '/home/runner/work/snck-panel/snck-panel',
				'JAVA_HOME_11_X64' => '/usr/lib/jvm/temurin-11-jdk-amd64',
				'COMPOSER_NO_INTERACTION' => '1',
				'JAVA_HOME_25_X64' => '/usr/lib/jvm/temurin-25-jdk-amd64',
				'GITHUB_PATH' => '/home/runner/work/_temp/_runner_file_commands/add_path_d130833a-5b64-4306-b325-d833227cd6e4',
				'GITHUB_ACTION' => '__run_7',
				'JAVA_HOME' => '/usr/lib/jvm/temurin-25-jdk-amd64',
				'GITHUB_RUN_NUMBER' => '31',
				'RUNNER_NAME' => 'GitHub Actions 1000001053',
				'GRADLE_HOME' => '/usr/share/gradle-9.9.1',
				'GITHUB_REPOSITORY_OWNER_ID' => '201361410',
				'ACTIONS_RUNNER_ACTION_ARCHIVE_CACHE' => '/opt/actionarchivecache',
				'XDG_CONFIG_HOME' => '/home/runner/.config',
				'MEMORY_PRESSURE_WRITE' => 'c29tZSAyMDAwMDAgMjAwMDAwMAA=',
				'DOTNET_SKIP_FIRST_TIME_EXPERIENCE' => '1',
				'ANT_HOME' => '/usr/share/ant',
				'GITHUB_TRIGGERING_ACTOR' => 'SnckBoy',
				'GITHUB_REF_TYPE' => 'branch',
				'HOMEBREW_CLEANUP_PERIODIC_FULL_DAYS' => '3650',
				'ACTIONS_RUNNER_RETURN_JOB_RESULT_FOR_HOSTED' => '1',
				'ANDROID_NDK' => '/usr/local/lib/android/sdk/ndk/27.3.13750724',
				'BOOTSTRAP_HASKELL_NONINTERACTIVE' => '1',
				'PWD' => '/home/runner/work/snck-panel/snck-panel/laravel-panel',
				'PIPX_BIN_DIR' => '/opt/pipx_bin',
				'LOGNAME' => 'runner',
				'GITHUB_ARTIFACTS_LIST' => '/home/runner/work/_temp/_runner_file_commands/artifacts_list_d130833a-5b64-4306-b325-d833227cd6e4',
				'GITHUB_REPOSITORY_ID' => '1340887319',
				'GITHUB_ACTIONS' => 'true',
				'USE_BAZEL_FALLBACK_VERSION' => 'silent:',
				'ANDROID_NDK_LATEST_HOME' => '/usr/local/lib/android/sdk/ndk/30.0.16248370',
				'SYSTEMD_EXEC_PID' => '1950',
				'GITHUB_SHA' => '721797ac4c86028926c603ad345e71a2bb3a68ca',
				'GITHUB_WORKFLOW_REF' => 'SnckBoy/snck-panel/.github/workflows/composer-security-refresh.yml@refs/heads/feat/hosting-audit-console',
				'POWERSHELL_DISTRIBUTION_CHANNEL' => 'GitHub-Actions-Linux',
				'RUNNER_ENVIRONMENT' => 'github-hosted',
				'DOTNET_MULTILEVEL_LOOKUP' => '0',
				'GITHUB_REF' => 'refs/heads/feat/hosting-audit-console',
				'RUNNER_OS' => 'Linux',
				'GITHUB_REF_PROTECTED' => 'false',
				'HOME' => '/home/runner',
				'GITHUB_API_URL' => 'https://api.github.com',
				'LANG' => 'C.UTF-8',
				'GOROOT_1_25_X64' => '/opt/hostedtoolcache/go/1.25.14/x64',
				'RUNNER_TRACKING_ID' => 'github_96598175-8acc-43d5-8a6f-25523c4ad3a8',
				'RUNNER_ARCH' => 'X64',
				'MEMORY_PRESSURE_WATCH' => '/sys/fs/cgroup/system.slice/hosted-compute-agent.service/memory.pressure',
				'RUNNER_TEMP' => '/home/runner/work/_temp',
				'GITHUB_STATE' => '/home/runner/work/_temp/_runner_file_commands/save_state_d130833a-5b64-4306-b325-d833227cd6e4',
				'EDGEWEBDRIVER' => '/usr/local/share/edge_driver',
				'JAVA_HOME_21_X64' => '/usr/lib/jvm/temurin-21-jdk-amd64',
				'GITHUB_ENV' => '/home/runner/work/_temp/_runner_file_commands/set_env_d130833a-5b64-4306-b325-d833227cd6e4',
				'GITHUB_EVENT_PATH' => '/home/runner/work/_temp/_github_workflow/event.json',
				'INVOCATION_ID' => '76ff0893655e4a67933ba22548684282',
				'GITHUB_EVENT_NAME' => 'push',
				'GITHUB_RUN_ID' => '43865657341',
				'JAVA_HOME_17_X64' => '/usr/lib/jvm/temurin-17-jdk-amd64',
				'ANDROID_NDK_HOME' => '/usr/local/lib/android/sdk/ndk/27.3.13750724',
				'COMPOSER_PROCESS_TIMEOUT' => '0',
				'GITHUB_STEP_SUMMARY' => '/home/runner/work/_temp/_runner_file_commands/step_summary_d130833a-5b64-4306-b325-d833227cd6e4',
				'HOMEBREW_NO_AUTO_UPDATE' => '1',
				'GITHUB_ACTOR' => 'SnckBoy',
				'NVM_DIR' => '/home/runner/.nvm',
				'SGX_AESM_ADDR' => '1',
				'GITHUB_RUN_ATTEMPT' => '1',
				'ANDROID_HOME' => '/usr/local/lib/android/sdk',
				'GITHUB_GRAPHQL_URL' => 'https://api.github.com/graphql',
				'ACCEPT_EULA' => 'Y',
				'USER' => 'runner',
				'PSModulePath' => '/root/.local/share/powershell/Modules:/usr/local/share/powershell/Modules:/opt/microsoft/powershell/7/Modules:/usr/share/az_15.6.1',
				'GITHUB_SERVER_URL' => 'https://github.com',
				'PIPX_HOME' => '/opt/pipx',
				'GECKOWEBDRIVER' => '/usr/local/share/gecko_driver',
				'CHROMEWEBDRIVER' => '/usr/local/share/chromedriver-linux64',
				'SHLVL' => '1',
				'ANDROID_SDK_ROOT' => '/usr/local/lib/android/sdk',
				'VCPKG_INSTALLATION_ROOT' => '/usr/local/share/vcpkg',
				'GITHUB_ACTOR_ID' => '201361410',
				'ACTIONS_ORCHESTRATION_ID' => 'ed8a6fb7-0dd6-888b-86d0-7f8253b4dc36.refresh.__default',
				'RUNNER_TOOL_CACHE' => '/opt/hostedtoolcache',
				'ImageVersion' => '20261111.196.1',
				'DOTNET_NOLOGO' => '1',
				'GITHUB_ARTIFACTS' => '/home/runner/work/_temp/_runner_file_commands/artifacts_d130833a-5b64-4306-b325-d833227cd6e4',
				'GITHUB_WORKFLOW_SHA' => '721797ac4c86028926c603ad345e71a2bb3a68ca',
				'GOROOT_1_24_X64' => '/opt/hostedtoolcache/go/1.24.13/x64',
				'GITHUB_REF_NAME' => 'feat/hosting-audit-console',
				'GITHUB_JOB' => 'refresh',
				'XDG_RUNTIME_DIR' => '/run/user/1001',
				'AZURE_EXTENSION_DIR' => '/opt/az/azcliextensions',
				'GOROOT_1_26_X64' => '/opt/hostedtoolcache/go/1.26.11/x64',
				'GITHUB_REPOSITORY' => 'SnckBoy/snck-panel',
				'CHROME_BIN' => '/usr/bin/google-chrome',
				'ANDROID_NDK_ROOT' => '/usr/local/lib/android/sdk/ndk/27.3.13750724',
				'GITHUB_RETENTION_DAYS' => '90',
				'JOURNAL_STREAM' => '10:15013',
				'RUNNER_WORKSPACE' => '/home/runner/work/snck-panel',
				'GITHUB_ACTION_REPOSITORY' => '',
				'COMPOSER_NO_AUDIT' => '1',
				'HCA_CLOUD_PROVIDER' => 'azure',
				'PATH' => '/opt/hostedtoolcache/setup-php/tools:/home/runner/.composer/vendor/bin:/snap/bin:/home/runner/.local/bin:/opt/pipx_bin:/home/runner/.cargo/bin:/home/runner/.config/composer/vendor/bin:/usr/local/.ghcup/bin:/home/runner/.dotnet/tools:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/games:/usr/local/games:/snap/bin',
				'GITHUB_BASE_REF' => '',
				'GHCUP_INSTALL_BASE_PREFIX' => '/usr/local',
				'CI' => 'true',
				'SWIFT_PATH' => '/usr/share/swift/usr/bin',
				'ImageOS' => 'ubuntu26',
				'GITHUB_REPOSITORY_OWNER' => 'SnckBoy',
				'GITHUB_HEAD_REF' => '',
				'GITHUB_ACTION_REF' => '',
				'ENABLE_RUNNER_TRACING' => 'true',
				'GITHUB_WORKFLOW' => 'Composer security refresh',
				'DEBIAN_FRONTEND' => 'noninteractive',
				'GITHUB_OUTPUT' => '/home/runner/work/_temp/_runner_file_commands/set_output_d130833a-5b64-4306-b325-d833227cd6e4',
				'AGENT_TOOLSDIRECTORY' => '/opt/hostedtoolcache',
				'_' => './vendor/bin/phpstan',
				'PHPRC' => '/tmp/phpstan-restart-ini-Ftdmga',
				'PHP_INI_SCAN_DIR' => '',
				'PHPSTAN_RESTART' => 'phpstan_turbo,opcache',
				'LINES' => '50',
				'COLUMNS' => '80',
				'SHELL_VERBOSITY' => '0',
			],
		];
	}


	protected function getDynamicParameter($key)
	{
		switch (true) {
			case $key === 'singleReflectionFile': return null;
			case $key === 'singleReflectionInsteadOfFile': return null;
			case $key === 'analysedPaths': return null;
			case $key === 'analysedPathsFromConfig': return null;
			case $key === 'sysGetTempDir': return sys_get_temp_dir();
			case $key === 'pro': return [
			'dnsServers' => ['1.1.1.2'],
			'tmpDir' => implode('', ['', sys_get_temp_dir(), '/phpstan-fixer']),
		];
			default: return parent::getDynamicParameter($key);
		};
	}


	public function getParameters(): array
	{
		array_map([$this, 'getParameter'], [
			'bootstrapFiles',
			'excludePaths',
			'level',
			'paths',
			'exceptions',
			'featureToggles',
			'fileExtensions',
			'checkAdvancedIsset',
			'reportAlwaysTrueInLastCondition',
			'checkClassCaseSensitivity',
			'checkExplicitMixed',
			'checkImplicitMixed',
			'checkFunctionArgumentTypes',
			'checkFunctionNameCase',
			'checkInternalClassCaseSensitivity',
			'checkMissingCallableSignature',
			'checkMissingVarTagTypehint',
			'checkArgumentsPassedByReference',
			'checkMaybeUndefinedVariables',
			'checkNullables',
			'checkThisOnly',
			'checkUnionTypes',
			'checkBenevolentUnionTypes',
			'checkExplicitMixedMissingReturn',
			'checkPhpDocMissingReturn',
			'checkPhpDocMethodSignatures',
			'checkExtraArguments',
			'checkMissingTypehints',
			'checkTooWideParameterOutInProtectedAndPublicMethods',
			'checkTooWideReturnTypesInProtectedAndPublicMethods',
			'checkTooWideThrowTypesInProtectedAndPublicMethods',
			'checkUninitializedProperties',
			'checkDynamicProperties',
			'strictRulesInstalled',
			'deprecationRulesInstalled',
			'inferPrivatePropertyTypeFromConstructor',
			'checkStrictPrintfPlaceholderTypes',
			'reportMaybes',
			'reportMaybesInMethodSignatures',
			'reportMaybesInPropertyPhpDocTypes',
			'reportStaticMethodSignatures',
			'reportWrongPhpDocTypeInVarTag',
			'reportAnyTypeWideningInVarTag',
			'reportNonIntStringArrayKey',
			'reportUnsafeArrayStringKeyCasting',
			'reportPossiblyNonexistentGeneralArrayOffset',
			'reportPossiblyNonexistentConstantArrayOffset',
			'checkMissingOverrideMethodAttribute',
			'checkMissingOverridePropertyAttribute',
			'mixinExcludeClasses',
			'scanFiles',
			'scanDirectories',
			'parallel',
			'phpVersion',
			'polluteScopeWithLoopInitialAssignments',
			'polluteScopeWithAlwaysIterableForeach',
			'polluteScopeWithBlock',
			'propertyAlwaysWrittenTags',
			'propertyAlwaysReadTags',
			'additionalConstructors',
			'treatPhpDocTypesAsCertain',
			'usePathConstantsAsConstantString',
			'rememberPossiblyImpureFunctionValues',
			'tips',
			'tipsOfTheDay',
			'reportMagicMethods',
			'reportMagicProperties',
			'ignoreErrors',
			'internalErrorsCountLimit',
			'cache',
			'reportUnmatchedIgnoredErrors',
			'reportIgnoresWithoutComments',
			'typeAliases',
			'universalObjectCratesClasses',
			'stubFiles',
			'earlyTerminatingMethodCalls',
			'earlyTerminatingFunctionCalls',
			'resultCachePath',
			'resultCacheSkipIfOlderThanDays',
			'resultCacheChecksProjectExtensionFilesDependencies',
			'dynamicConstantNames',
			'customRulesetUsed',
			'editorUrl',
			'editorUrlTitle',
			'errorFormat',
			'sysGetTempDir',
			'sourceLocatorPlaygroundMode',
			'pro',
			'__validate',
			'parametersNotInvalidatingCache',
			'checkOctaneCompatibility',
			'noEnvCallsOutsideOfConfig',
			'noModelMake',
			'noImplicitQueryBuilderCall',
			'noUnnecessaryCollectionCall',
			'noUnnecessaryCollectionCallOnly',
			'noUnnecessaryCollectionCallExcept',
			'noUnnecessaryEnumerableToArrayCalls',
			'squashedMigrationsPath',
			'databaseMigrationsPath',
			'disableMigrationScan',
			'disableSchemaScan',
			'configDirectories',
			'viewDirectories',
			'translationDirectories',
			'checkModelProperties',
			'checkUnusedViews',
			'checkMissingTranslations',
			'checkModelAppends',
			'checkModelMethodVisibility',
			'generalizeEnvReturnType',
			'checkConfigTypes',
			'checkAuthCallsWhenInRequestScope',
			'parseModelCastsMethod',
			'enableMigrationCache',
			'checkUniqueJobUniqueFor',
			'checkUniqueJobUniqueId',
			'noBatchedUniqueJob',
			'checkJobSerializesModels',
			'checkBatchedJobIsBatchable',
			'checkBatchableJobChecksCancellation',
			'checkDispatchInTransactionAfterCommit',
			'tmpDir',
			'debugMode',
			'productionMode',
			'tempDir',
			'rootDir',
			'currentWorkingDirectory',
			'cliArgumentsVariablesRegistered',
			'additionalConfigFiles',
			'allConfigFiles',
			'composerAutoloaderProjectPaths',
			'generateBaselineFile',
			'usedLevel',
			'cliAutoloadFile',
			'env',
			'singleReflectionFile',
			'singleReflectionInsteadOfFile',
			'analysedPaths',
			'analysedPathsFromConfig',
		]);
		return parent::getParameters();
	}
}
