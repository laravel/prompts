<?php

use function Laravel\Prompts\tabbedscrollableselect;

require __DIR__.'/../vendor/autoload.php';

$application = tabbedscrollableselect(
    label: 'Which persons application would you like to choose?',
    options: [
        [
            'id' => 0,
            'tab' => 'Jess Archer',
            'body' => <<<BODY
            Subject: Application for Software Developer Position - The Code to Success!

            Dear Hiring Manager,
            
            I am writing to apply for the Software Developer position at [Company Name], a role I am extremely excited about as it seems tailor-made for someone with my background and unique blend of skills – I'm not just another cog in the machine, but a potential key in your company's codebase! Having honed my programming skills in various languages, I am confident in my ability to debug your expectations and log significant achievements. My previous experience at Tech Innovations, where I developed high-quality, scalable code, was not just a job but a daily opportunity to go through "exceptional" handling!
            
            Why do I think I'm a great fit for your team? Well, it's not only because I can navigate through complex algorithms like a pro, but also because I believe that a good coder knows how to iterate over coffee cups and lines of code alike. My friends say I'm like a human debugger, always ready to sort out arrays of problems – and they're not wrong. At my current job, they don't use Java because when I touched the code, the coffee machine started programming itself!
            
            In my quest to find the perfect role, I've interfaced with many APIs, but none seem to offer a RESTful career path quite like [Company Name]. I am eager to bring my skills to your innovative projects, and together, we can push the envelope, or should I say, "push the commit"? I am especially impressed by your recent project on [specific project], and I have some ideas that might optimize our runtime by reducing the time complexity of making an impact.
            
            Thank you for considering my application. I look forward to the possibility of discussing how I can contribute to your team and help [Company Name] continue to excel and debug the myth that all software development is just about coding. Let's set up a time to connect - I promise it won't be a "hard commit"!
            
            Warm regards,
            
            [Your Name]
            BODY,
        ],
        [
            'id' => 1,
            'tab' => 'Joe Dixon',
            'body' => <<<BODY
            Subject: Innovative Applicant Alert: Ready to Engineer Success at [Company Name]!

            Dear Hiring Manager,

            It's not every day you find an applicant who can seamlessly integrate into any team, debug complex problems, and still manage to be the life of the code party! My name is [Your Name], and I'm thrilled to submit my resume for the Software Developer position at [Company Name]. Not only do I bring a robust portfolio of programming skills to the table, but I also carry a toolkit filled with enthusiasm, creativity, and a knack for turning challenges into checkpoints. I've spent the past few years at [Previous Company] not only pushing code but pushing the boundaries of what our applications could achieve—imagine what I could do with your state-of-the-art resources!

            Now, let's talk about why I'm a perfect match for your team. You need someone who knows their way around code, certainly. But what about navigating through Monday mornings and tight deadlines? Fear not, because I excel in optimizing coffee consumption while minimizing bug production. My previous project manager often said I could find the "root" in any "tree" and the fun in any function, making me an asset in both team dynamics and product development.

            I am particularly impressed by [Company Name]'s recent foray into [specific technology or project], and I am buzzing with ideas that could further enhance its scalability and performance. With my proactive approach and your company's innovative culture, we could very well be the next big breakpoint in the industry! I am all set to help [Company Name] not only meet its goals but exceed them with flying colors (and yes, I mean both the syntax highlighting and the business metrics).

            Thank you for considering my application. I am looking forward to the opportunity to dive deep into a conversation with you and explore how my background, skills, and enthusiasms align with the vision of [Company Name]. Let's synchronize our calendars for a chat—I assure you, it will be more engaging than a silent console!

            Best regards,

            [Your Name]
            BODY,
        ],
        [
            'id' => 2,
            'tab' => 'Tim MacDonald',
            'body' => <<<BODY
            Subject: Ready to Commit: My Application for Software Developer at [Company Name]

            Dear Hiring Team,

            Hello from a passionate coder who not only loves semicolons; but also knows where to put them! My name is [Your Name], and I am enthusiastic about the opportunity to join [Company Name] as a Software Developer. With a rich background in software engineering complemented by my quick-witted problem-solving skills, I am prepared to contribute to your innovative projects and ensure that every loop in our code is as smooth as the user interfaces we design.

            I've always believed that a good developer is like a good comedian; they need timing, creativity, and the ability to keep an audience—whether users or fellow coders—engaged. My experience at [Previous Company] taught me to manage databases and deadlines with a smile, and I've successfully led several projects from concept through to debugging and deployment. At [Company Name], I'm eager to bring laughter and high-quality code to the table, making sure that our productivity and spirits are always compiling smoothly.

            Your commitment to [specific technology or project] is particularly compelling to me. Having followed your team's achievements through industry publications and conferences, I am excited about the prospect of contributing my own ideas on [a technology or approach]. Together, I believe we can enhance your systems to not only perform efficiently but also deliver a punchline of power and precision that the industry will notice.

            I appreciate your consideration of my application and am looking forward to the opportunity to further discuss how my programming prowess and proactive attitude can be of great benefit to [Company Name]. I'm ready to checkout my current position and push forward with your team. Let's connect and discuss how we can sync our goals and start scripting our next success story!

            Warmest regards,

            [Your Name]
            BODY,
        ],
        [
            'id' => 3,
            'tab' => 'Mohammed Said',
            'body' => <<<BODY
            Subject: Coding My Way Into Your Team: Software Developer Application at [Company Name]

            Dear Hiring Manager,

            I am [Your Name], and I'm sending this application encoded with enthusiasm and a track record of success in the tech industry, hoping to join [Company Name] as a Software Developer. With a knack for cracking code faster than most people crack a smile, I've been a dynamic part of tech teams that thrive on creativity and precision—just like your esteemed company. I believe that with my blend of skills, humor, and dedication, I can contribute significantly to your innovative projects and vibrant team culture.

            Why am I excited about the possibility of working with you? Beyond my love for crafting clean, efficient code, I thrive in environments that challenge the status quo and reward agility and innovation. At my current position with [Previous Company], I've not only debugged and developed, but also delivered pun-tastic presentations that have made sprint reviews as anticipated as the latest software release. My approach has always been to think outside the "box model," applying both logical and creative thinking to solve complex problems.

            I am particularly impressed by [Company Name]'s recent work on [specific project or technology], which I believe aligns perfectly with my expertise in [related technology or skill]. I am eager to bring my array of skills from [specific languages or technologies you are proficient in] to your team, ensuring that together, we can enhance user experiences and backend functionality in ways that are not only functional but also fun.

            Thank you for considering my application. I am looking forward to the opportunity to discuss how my technical skills, light-hearted approach, and passion for software development can be effectively integrated into your team. Let's schedule a meeting to decode the possibilities together. I am confident that it will be a productive and cheerful dialogue, or my name isn’t [Your Name]—and trust me, it is!

            Best regards,

            [Your Name]
            BODY,
        ],
        [
            'id' => 4,
            'tab' => 'Nuno Maduro',
            'body' => <<<BODY
            Subject: Debugging Opportunities: Application for Software Developer at [Company Name]

            Dear Hiring Team,

            Greetings! I'm [Your Name], a software developer with a penchant for turning complex, buggy code into sleek, efficient applications. I'm applying for the Software Developer position at [Company Name], drawn by your commitment to innovation and quality, and the humorous yet professional spirit of your company culture. My background in [Your Specialty or Previous Experience] has equipped me with the tools to enhance your projects and add a little extra byte to your team's dynamic.

            In my current role at [Previous Company], I’ve proven my ability to manage and improve software systems, delivering solutions that not only meet but exceed expectations. I approach each project with the mindset of a 'code surgeon,' meticulously refining and optimizing to ensure peak performance. But it’s not all serious—my colleagues often say I add a 'debugging delight' to our team meetings, making even the most complex problem-solving sessions enjoyable with my puns and quick wit.

            I am particularly enthusiastic about [Company Name]'s recent initiatives in [specific field or project], and I am eager to bring my experience in [specific technology or method] to your esteemed team. Your project aligns seamlessly with my skills and ambitions, and I am excited about the prospect of contributing to your continued success. I'm looking to not only push code but also push the boundaries of what we can achieve together with a mix of innovation, collaboration, and a few well-timed jokes!

            Thank you for considering my application. I look forward to the possibility of joining your team and contributing to [Company Name]'s future projects. I am keen to discuss how my background, skills, and enthusiasms align with the goals of your company. Let’s connect to compile our thoughts and begin scripting a successful chapter together.

            Warmest regards,

            [Your Name]
            BODY,
        ],
    ],
    default: 0,
    scroll: 14,
    max_width: 120,
    required: false,
    // validate: fn ($values) => match (true) {
    //     empty($values) => 'Please select at least one permission.',
    //     default => null,
    // },
    hint: 'The chosen application will determine who gets the job.',
);

var_dump($application);

echo str_repeat(PHP_EOL, 1);
