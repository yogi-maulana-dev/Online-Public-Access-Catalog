<?php
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
$e = fn ($v) => htmlspecialchars((string) $v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
$idFor = fn ($book) => 'oai:'.request()->getHost().':'.$book->slug;
?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
    <responseDate>{{ $responseDate }}</responseDate>
    <request verb="{{ $verb }}">{{ url('/oai') }}</request>

@if(!empty($error))
    <error code="{{ $error['code'] }}">{{ $error['message'] }}</error>
@elseif($verb === 'Identify')
    <Identify>
        <repositoryName>University Press OAI Repository</repositoryName>
        <baseURL>{{ url('/oai') }}</baseURL>
        <protocolVersion>2.0</protocolVersion>
        <adminEmail>admin@press.test</adminEmail>
        <earliestDatestamp>{{ $earliest }}</earliestDatestamp>
        <deletedRecord>no</deletedRecord>
        <granularity>YYYY-MM-DDThh:mm:ssZ</granularity>
    </Identify>
@elseif($verb === 'ListMetadataFormats')
    <ListMetadataFormats>
        <metadataFormat>
            <metadataPrefix>oai_dc</metadataPrefix>
            <schema>http://www.openarchives.org/OAI/2.0/oai_dc.xsd</schema>
            <metadataNamespace>http://www.openarchives.org/OAI/2.0/oai_dc/</metadataNamespace>
        </metadataFormat>
    </ListMetadataFormats>
@elseif($verb === 'ListSets')
    <ListSets>
        @foreach($sets as $set)
        <set><setSpec>{{ $set->slug }}</setSpec><setName>{{ $e($set->name) }}</setName></set>
        @endforeach
    </ListSets>
@elseif($verb === 'ListIdentifiers')
    <ListIdentifiers>
        @foreach($books as $book)
        <header>
            <identifier>{{ $idFor($book) }}</identifier>
            <datestamp>{{ optional($book->published_at)->toIso8601ZuluString() }}</datestamp>
            @if($book->category)<setSpec>{{ $book->category->slug }}</setSpec>@endif
        </header>
        @endforeach
    </ListIdentifiers>
@elseif(in_array($verb, ['ListRecords', 'GetRecord']))
    <{{ $verb }}>
        @foreach($books as $book)
        <record>
            <header>
                <identifier>{{ $idFor($book) }}</identifier>
                <datestamp>{{ optional($book->published_at)->toIso8601ZuluString() }}</datestamp>
                @if($book->category)<setSpec>{{ $book->category->slug }}</setSpec>@endif
            </header>
            <metadata>
                <oai_dc:dc xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/"
                           xmlns:dc="http://purl.org/dc/elements/1.1/"
                           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                           xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd">
                    <dc:title>{{ $e($book->title) }}</dc:title>
                    @foreach($book->authors as $author)<dc:creator>{{ $e($author->name) }}</dc:creator>@endforeach
                    @foreach($book->keywords as $kw)<dc:subject>{{ $e($kw->name) }}</dc:subject>@endforeach
                    <dc:description>{{ $e(strip_tags((string) $book->abstract)) }}</dc:description>
                    @if($book->publisher)<dc:publisher>{{ $e($book->publisher->name) }}</dc:publisher>@endif
                    <dc:date>{{ $book->year }}</dc:date>
                    <dc:type>Text</dc:type>
                    <dc:format>application/pdf</dc:format>
                    <dc:identifier>{{ route('books.show', $book->slug) }}</dc:identifier>
                    @if($book->doi)<dc:identifier>https://doi.org/{{ $e($book->doi) }}</dc:identifier>@endif
                    @if($book->isbn)<dc:identifier>ISBN:{{ $e($book->isbn) }}</dc:identifier>@endif
                    <dc:language>{{ $e($book->language) }}</dc:language>
                    @if($book->license)<dc:rights>{{ $e($book->license) }}</dc:rights>@endif
                </oai_dc:dc>
            </metadata>
        </record>
        @endforeach
    </{{ $verb }}>
@endif
</OAI-PMH>
