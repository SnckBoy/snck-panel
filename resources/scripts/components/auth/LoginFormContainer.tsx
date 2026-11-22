import React, { forwardRef } from 'react';
import { Form } from 'formik';
import styled from 'styled-components/macro';
import FlashMessageRender from '@/components/FlashMessageRender';
import tw from 'twin.macro';

type Props = React.DetailedHTMLProps<React.FormHTMLAttributes<HTMLFormElement>, HTMLFormElement> & {
    title?: string;
};

const Container = styled.div`
    width: 100%;
    max-width: 760px;
    margin: 0 auto;
    padding: 0 1rem;

    @media (min-width: 768px) {
        padding: 1.5rem;
    }
`;

const GlassPanel = styled.div`
    display: flex;
    align-items: center;
    gap: clamp(1.5rem, 4vw, 3rem);
    width: 100%;
    padding: clamp(1.25rem, 4vw, 2.75rem);
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.13);
    border-radius: 18px;
    background: rgba(7, 10, 13, 0.76);
    box-shadow:
        0 24px 70px rgba(0, 0, 0, 0.48),
        inset 0 1px 0 rgba(255, 255, 255, 0.055);
    -webkit-backdrop-filter: blur(20px) saturate(125%);
    backdrop-filter: blur(20px) saturate(125%);

    input:not([type='checkbox']):not([type='radio']) {
        color: #f1f3f2 !important;
        background: rgba(255, 255, 255, 0.055) !important;
        border: 1px solid rgba(255, 255, 255, 0.16) !important;
        border-radius: 8px;
        box-shadow: none !important;
    }

    input:not([type='checkbox']):not([type='radio']):focus {
        background: rgba(255, 255, 255, 0.075) !important;
        border-color: #c99a55 !important;
        box-shadow: 0 0 0 2px rgba(201, 154, 85, 0.18) !important;
    }

    input::placeholder {
        color: rgba(225, 231, 228, 0.48);
    }

    @media (max-width: 767px) {
        flex-direction: column;
        gap: 1.5rem;
        padding: 1.5rem 1.15rem;
        border-radius: 14px;
    }

    @media (prefers-reduced-transparency: reduce) {
        background: #0c1013;
        -webkit-backdrop-filter: none;
        backdrop-filter: none;
    }
`;

const Brand = styled.div`
    display: flex;
    flex: 0 0 38%;
    align-items: center;
    justify-content: center;
    min-width: 0;

    img {
        display: block;
        width: 100%;
        max-width: 245px;
        height: auto;
        object-fit: contain;
        filter: drop-shadow(0 12px 30px rgba(0, 0, 0, 0.42));
    }

    @media (max-width: 767px) {
        flex: none;

        img {
            width: 150px;
            max-height: 100px;
        }
    }
`;

const FormContent = styled.div`
    flex: 1 1 auto;
    min-width: 0;
    width: 100%;
`;

const Heading = styled.h2`
    margin: 0 0 1.5rem;
    color: #f1f3f2;
    font-size: clamp(1.45rem, 3vw, 1.9rem);
    font-weight: 650;
    letter-spacing: -0.025em;
    line-height: 1.2;
    text-align: left;

    @media (max-width: 767px) {
        margin-bottom: 1.25rem;
        text-align: center;
    }
`;

const Footer = styled.p`
    margin: 1.25rem 0 0;
    color: rgba(220, 226, 223, 0.55);
    font-size: 0.75rem;
    text-align: center;

    a {
        color: rgba(220, 226, 223, 0.72);
        text-decoration: none;
        transition: color 120ms ease;
    }

    a:hover {
        color: #d8b47f;
    }
`;

export default forwardRef<HTMLFormElement, Props>(({ title, ...props }, ref) => (
    <Container>
        <FlashMessageRender css={tw`mb-3`} />
        <Form {...props} ref={ref}>
            <GlassPanel>
                <Brand>
                    <img src={'/assets/svgs/snck.svg'} alt={'SNCK Panel'} />
                </Brand>
                <FormContent>
                    {title && <Heading>{title}</Heading>}
                    {props.children}
                </FormContent>
            </GlassPanel>
        </Form>
        <Footer>
            &copy; 2015 - {new Date().getFullYear()}&nbsp;
            <a
                rel={'noopener nofollow noreferrer'}
                href={'https://github.com/SnckBoy/snck-panel'}
                target={'_blank'}
            >
                SNCK Panel
            </a>
        </Footer>
    </Container>
));
